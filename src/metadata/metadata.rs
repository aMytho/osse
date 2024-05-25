use sea_orm::entity::prelude::DateTime;

/**
 * The info we need to write a track to the DB
 */
pub struct FileMetadata {
    pub album: Option<String>,
    pub artist: Option<i32>,
    pub title: Option<String>,
    pub updated_at: Option<DateTime>,
    pub path: String,
    pub size: u64,
    pub bitrate: Option<i32>,
    pub duration: u64,
}

impl FileMetadata {
    pub fn new() -> FileMetadata {
        FileMetadata {
            artist: None,
            album: None,
            title: None,
            updated_at: None,
            path: String::from(""),
            size: 0,
            bitrate: None,
            duration: 0
        }
    }
}

pub struct CoverArt {
    pub mime_type: String,
    pub data: Box<[u8]>
}