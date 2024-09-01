use time::PrimitiveDateTime;

/**
 * The info we need to write a track to the DB
 */
#[derive(Debug)]
pub struct FileMetadata {
    pub album: Option<String>,
    pub album_id: Option<i32>,
    pub album_artist: Option<String>,
    pub album_artist_id: Option<i32>,
    pub artist: Option<i32>,
    pub title: Option<String>,
    pub updated_at: Option<PrimitiveDateTime>,
    pub path: String,
    pub size: u64,
    pub bitrate: Option<i32>,
    pub duration: u64,
    pub year: Option<i32>,
    pub track_number: Option<i32>,
    pub album_year: Option<i32>
}

impl FileMetadata {
    pub fn new() -> FileMetadata {
        FileMetadata {
            artist: None,
            album: None,
            album_id: None,
            album_artist: None,
            album_artist_id: None,
            title: None,
            updated_at: None,
            path: String::from(""),
            size: 0,
            bitrate: None,
            duration: 0,
            year: None,
            track_number: None,
            album_year: None
        }
    }
}

// Data from a tag. These fields will be used on the FileMetadata
pub struct TagMetadata {
    pub album: Option<String>,
    pub album_artist: Option<String>,
    pub artist: Option<i32>,
    pub title: Option<String>,
    pub year: Option<u32>,
    pub track_index: Option<u32>,
    pub album_year: Option<i32>
}

impl TagMetadata {
    pub fn new() -> TagMetadata {
        TagMetadata {album: None, album_artist: None, artist: None, title: None, year: None, track_index: None, album_year: None}
    }
}

pub struct CoverArt {
    pub mime_type: String,
    pub data: Box<[u8]>,
}
