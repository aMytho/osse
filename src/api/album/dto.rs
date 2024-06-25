use serde::{Deserialize, Serialize};

use crate::entities::track::Model;


#[derive(Debug, Deserialize)]
pub struct AllAlbumsQuery {
    pub tracks: Option<bool>
}

#[derive(Serialize)]
pub struct AllAlbumsResponse (pub Vec<AlbumResponse>);

#[derive(Serialize)]
pub struct AlbumResponse {
    pub id: i32,
    pub name: String,
    pub artist_id: Option<i32>,
    pub tracks: Option<Vec<Model>>
}
