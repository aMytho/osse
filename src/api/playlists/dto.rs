use serde::{Deserialize, Serialize};

use crate::entities::playlist::Playlist;

#[derive(Deserialize)]
pub struct CreatePlaylist {
    pub playlist_id: i32,
    pub track_id: i32
}

#[derive(Serialize)]
pub struct PlaylistDto {
    pub id: i32,
    pub name: String,
    pub count: i64,
    // Temp val to make an empty array
    pub tracks: Vec<bool>
}

impl PlaylistDto {
    pub fn to_model(playlist: Playlist, count: i64) -> Self {
        PlaylistDto {
            count,
            id: playlist.id,
            name: playlist.name,
            tracks: Vec::new()
        }
    }
}
