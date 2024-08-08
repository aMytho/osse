use serde::{Deserialize, Serialize};

use crate::entities::track::Track;

#[derive(Deserialize)]
pub struct GetCoverArtById {
    pub id: i32
}

#[derive(Deserialize)]
pub struct TrackSearch {
    pub track: Option<String>,
    pub track_offset: Option<i64>,
}

#[derive(Serialize)]
pub struct TrackSearchResult {
    pub tracks: Vec<Track>
}