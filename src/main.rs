mod args;
mod config;
mod schema;
mod files;
mod metadata;
mod entities;
mod api;

use diesel::sqlite::SqliteConnection;
use api::album::album_controller::{get_album, get_album_tracks};
use config::AppConfig;
use diesel::r2d2::{ConnectionManager, Pool};
use poem::http::Method;
use poem::RouteMethod;
use poem::{post, EndpointExt, middleware::Cors};
use poem::{listener::TcpListener, Route, Server};
use crate::api::album::album_controller::get_all_albums;
use crate::api::shared::middleware::validate_track_query;
use crate::api::stream::middleware::validate_range;
use crate::api::stream::stream_controller::{stream_file, stream_file_header};
use crate::api::artists::artist_controller::{get_artist, get_all_artists};
use crate::api::tracks::track_controller::{get_all_tracks, scan, get_cover_art_for_track};

use api::server::ping;

#[derive(Debug, Clone)]
pub struct AppState {
    pub db: Pool<ConnectionManager<SqliteConnection>>,
    pub config: AppConfig
}

#[tokio::main]
async fn main() -> std::io::Result<()> {
    let config_path = args::get_config_path();
    let config = match config::load_config(config_path) {
        Ok(config) => config,
        Err(err) => panic!("Failed to load config. Error: {:?}", err)
    };

    println!("Config: {:?}", config);

    let pool = ConnectionManager::<SqliteConnection>::new(&config.database_address);
    let db = match Pool::builder().test_on_check_out(true).build(pool) {
        Ok(c) => c,
        Err(_) => panic!("Failed to connect to DB.")
    };

    println!("Starting HTTP server");

    let connection_string = format!("{}:{}", config.server_address.clone(), config.server_port);
    
    let cors = Cors::new()
    .allow_methods([Method::POST, Method::GET, Method::OPTIONS, Method::HEAD]);
    
    let app = Route::new()
        .at("/ping", ping)
        .at("/tracks/all", get_all_tracks)
        .at("/tracks/scan", post(scan))
        .at("/tracks/cover", get_cover_art_for_track)
        .at("/artists", get_artist)
        .at("/artists/all", get_all_artists)
        .at("/albums", get_all_albums)
        .at("/albums/:album_id", get_album)
        .at("/albums/:album_id/tracks", get_album_tracks)
        .at("/stream",
            RouteMethod::new()
                .get(stream_file.around(validate_range).around(validate_track_query))
                .head(stream_file_header.around(validate_track_query)))
        .with(cors)
        .data(AppState {db, config});
    
    Server::new(TcpListener::bind(connection_string))
        .run(app)
        .await
}
