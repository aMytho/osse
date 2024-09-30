use diesel::prelude::*;
use serde::Serialize;

use crate::metadata::metadata::FileMetadata;

use super::artist::Artist;
use time::PrimitiveDateTime;

#[derive(Clone, Serialize, Queryable, Selectable, Identifiable, Associations, Debug)]
#[diesel(belongs_to(Artist))]
#[diesel(table_name = crate::schema::tracks)]
pub struct Track {
    pub id: i32,
    pub title: String,
    pub artist_id: Option<i32>,
    pub duration: i32,
    pub size: i64,
    pub bitrate: Option<i32>,
    pub location: String,
    pub updated_at: PrimitiveDateTime,
    pub album_id: Option<i32>,
    pub year: Option<i32>,
    pub track_number: Option<i32>,
    pub disc_number: Option<i32>,
}

#[derive(Insertable)]
#[diesel(table_name = crate::schema::tracks)]
pub struct TrackForm {
    pub title: String,
    pub artist_id: Option<i32>,
    pub duration: i32,
    pub size: i64,
    pub bitrate: Option<i32>,
    pub location: String,
    pub updated_at: PrimitiveDateTime,
    pub album_id: Option<i32>,
    pub year: Option<i32>,
    pub track_number: Option<i32>,
    pub disc_number: Option<i32>,
}

impl TrackForm {
    pub fn from(file: &FileMetadata) -> TrackForm {
        TrackForm {
            album_id: file.album_id,
            artist_id: file.artist,
            duration: file.duration as i32,
            bitrate: file.bitrate,
            location: file.path.clone(),
            size: file.size as i64,
            title: file.title.clone().unwrap(),
            updated_at: file.updated_at.unwrap(),
            year: file.year,
            track_number: file.track_number,
            disc_number: file.disc_number,
        }
    }
}
