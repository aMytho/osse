mod args;
mod config;
mod global;
mod files;
mod metadata;
mod entities;
mod api;

use poem::http::Method;
use poem::RouteMethod;
use poem::{post, EndpointExt, middleware::Cors};
use poem::{listener::TcpListener, Route, Server};
use crate::api::album::album_controller::get_all_albums;
use crate::api::shared::middleware::{validate_track_header, validate_track_query};
use crate::api::stream::middleware::validate_range;
use crate::api::stream::stream_controller::{stream_file, stream_file_header};
use crate::api::artists::artist_controller::{get_artist, get_all_artists};
use crate::api::tracks::track_controller::{get_all_tracks, scan, get_cover_art_for_track};

use api::server::ping;

#[tokio::main]
async fn main() -> std::io::Result<()> {
    let config_path = args::get_config_path();
    let config = match config::load_config(config_path) {
        Ok(config) => config,
        Err(err) => panic!("Failed to load config. Error: {:?}", err)
    };

    println!("Config: {:?}", config);

    println!("Starting HTTP server");
    
    let cors = Cors::new()
    .allow_methods([Method::POST, Method::GET, Method::OPTIONS, Method::HEAD]);
    
    let app = Route::new()
        .at("/ping", ping)
        .at("/tracks/all", get_all_tracks)
        .at("/tracks/scan", post(scan))
        .at("/tracks/cover", get_cover_art_for_track)
        .at("/artists", get_artist)
        .at("/artists/all", get_all_artists)
        .at("/albums/all", get_all_albums)
        .at("/stream",
            RouteMethod::new()
                .get(stream_file.around(validate_range).around(validate_track_query))
                .head(stream_file_header.around(validate_track_query)))
        .with(cors);
    
    Server::new(TcpListener::bind(format!("{}:{}", config.server_address.clone(), config.server_port)))
        .run(app)
        .await
}
