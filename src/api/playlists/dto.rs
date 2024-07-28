use serde::Deserialize;

#[derive(Deserialize)]
pub struct CreatePlaylist {
    pub playlist_id: i32,
    pub track_id: i32
}
