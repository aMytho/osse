mod api;
mod args;
mod config;
mod entities;
mod files;
mod metadata;
mod schema;

use std::panic;

use crate::api::albums::album_controller::get_all_albums;
use crate::api::artists::artist_controller::{get_all_artists, get_artist};
use crate::api::shared::middleware::validate_track_query;
use crate::api::stream::middleware::validate_range;
use crate::api::stream::stream_controller::{stream_file, stream_file_header};
use crate::api::tracks::track_controller::{get_all_tracks, get_cover_art_for_track, scan};
use api::albums::album_controller::{get_album, get_album_tracks};
use api::playlists::middleware::valid_playlist;
use api::playlists::playlist_controller::{
    add_track_to_playlist, create_playlist, edit_playlist, get_all_playlists, get_playlist,
    get_playlist_tracks, remove_playlist, remove_playlist_tracks,
};
use api::shared::middleware::cache_control;
use api::tracks::track_controller::search_for_track;
use config::AppConfig;
use diesel::r2d2::{ConnectionManager, Pool};
use diesel::sqlite::SqliteConnection;
use diesel_migrations::{embed_migrations, EmbeddedMigrations, MigrationHarness};
use poem::http::Method;
use poem::RouteMethod;
use poem::{delete, middleware::Cors, post, EndpointExt};
use poem::{listener::TcpListener, Route, Server};

use api::server::{directories, ping, stats};

const MIGRATIONS: EmbeddedMigrations = embed_migrations!();

#[derive(Debug, Clone)]
pub struct AppState {
    pub db: Pool<ConnectionManager<SqliteConnection>>,
    pub config: AppConfig,
}

fn run_db_migrations(conn: &mut impl MigrationHarness<diesel::sqlite::Sqlite>) {
    conn.run_pending_migrations(MIGRATIONS)
        .expect("Failed to run migrations.");
}

#[tokio::main(flavor = "multi_thread")]
async fn main() -> std::io::Result<()> {
    let config_path = args::get_config_path();

    let config = match config::load_config(config_path) {
        Ok(config) => config,
        Err(err) => panic!("Failed to load config. Error: {:?}", err),
    };

    println!("Config: {:?}", config);

    let pool = ConnectionManager::<SqliteConnection>::new(&config.database_address);
    let db = match Pool::builder().test_on_check_out(true).build(pool) {
        Ok(c) => c,
        Err(_) => panic!("Failed to connect to DB."),
    };

    match db.get() {
        Ok(mut c) => {
            run_db_migrations(&mut c);
        }
        Err(_) => panic!("Failed to get a DB connection for migrtions."),
    };

    run_db_migrations(&mut db.get().unwrap());

    println!("Starting HTTP server");

    let connection_string = format!("{}:{}", config.server_address.clone(), config.server_port);

    let cors = Cors::new().allow_methods([
        Method::POST,
        Method::GET,
        Method::PATCH,
        Method::DELETE,
        Method::OPTIONS,
        Method::HEAD,
    ]);

    let app = Route::new()
        .at("/ping", ping.around(cache_control))
        .at("/stats", stats)
        .at("/config/directories", directories)
        .at("/tracks/all", get_all_tracks)
        .at("/tracks/search", search_for_track)
        .at("/tracks/scan", post(scan))
        .at(
            "/tracks/cover",
            get_cover_art_for_track.around(cache_control),
        )
        .at("/artists", get_artist.around(cache_control))
        .at("/artists/all", get_all_artists)
        .at("/albums", get_all_albums)
        .at("/albums/:album_id", get_album.around(cache_control))
        .at("/albums/:album_id/tracks", get_album_tracks)
        .at(
            "/playlists",
            RouteMethod::new()
                .get(get_all_playlists)
                .post(create_playlist),
        )
        .at(
            "/playlists/:playlist_id",
            RouteMethod::new()
                .get(get_playlist.around(cache_control))
                .delete(remove_playlist)
                .patch(edit_playlist),
        )
        .at("/playlists/:playlist_id/tracks", get_playlist_tracks)
        .at(
            "/playlists/:playlist_id/tracks/:track_id",
            delete(remove_playlist_tracks),
        )
        .at(
            "/playlists-tracks",
            post(add_track_to_playlist).around(valid_playlist),
        )
        .at(
            "/stream",
            RouteMethod::new()
                .get(
                    stream_file
                        .around(validate_range)
                        .around(validate_track_query),
                )
                .head(stream_file_header.around(validate_track_query)),
        )
        .with(cors)
        .data(AppState { db, config });

    Server::new(TcpListener::bind(connection_string))
        .run(app)
        .await
}
