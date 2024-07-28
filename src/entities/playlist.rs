use diesel::prelude::*;
use serde::Serialize;

use super::track::Track;

#[derive(Serialize)]
#[derive(Queryable, Selectable, Identifiable, Debug, Eq, PartialEq, Clone)]
#[diesel(table_name = crate::schema::playlists)]
#[diesel(primary_key(id))]
pub struct Playlist {
    pub id: i32,
    pub name: String,
}

impl Playlist {
    pub fn to_model(&self) -> (i32, String) {
        (self.id, self.name.clone())
    }
}

#[derive(Identifiable, Selectable, Queryable, Associations, Debug)]
#[diesel(belongs_to(Track))]
#[diesel(belongs_to(Playlist))]
#[diesel(table_name = crate::schema::playlist_tracks)]
#[diesel(primary_key(track_id, playlist_id))]
pub struct PlaylistTrack {
    pub track_id: i32,
    pub playlist_id: i32
}
