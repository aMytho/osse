use poem::{handler, web::{Data, Json}};
use serde::Serialize;

use crate::{api::{albums::album_service::AlbumService, playlists::playlist_service::PlaylistService, tracks::track_service::TrackService}, AppState};

use super::artists::artist_service::ArtistService;

#[handler]
pub fn ping() -> &'static str {
    "hello"
}

#[derive(Serialize)]
struct Stats {
    tracks: i64,
    artists: i64,
    albums: i64,
    playlists: i64
}

#[handler]
pub fn stats(state: Data<&AppState>) -> Json<Stats> {
    Json(Stats {
        albums: AlbumService::new(state.db.clone()).count().unwrap(),
        artists: ArtistService::new(state.db.clone()).count().unwrap(),
        tracks: TrackService::new(state.db.clone()).count().unwrap(),
        playlists: PlaylistService::new(state.db.clone()).count().unwrap(),
    })
}

#[handler]
pub fn directories(state: Data<&AppState>) -> Json<Vec<String>> {
    Json(state.0.config.files.clone())
}

