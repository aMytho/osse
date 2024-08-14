use lofty::picture::MimeType;
use time::{OffsetDateTime, PrimitiveDateTime};
use std::fs::Metadata;
use std::time::{SystemTime, UNIX_EPOCH};
use std::io::Error;

pub fn get_mime_type(mime: &MimeType) -> String {
    match mime {
        MimeType::Png => "image/png".to_owned(),
        MimeType::Jpeg => "image/jpeg".to_owned(),
        MimeType::Tiff => "image/tiff".to_owned(),
        MimeType::Bmp => "image/bmp".to_owned(),
        MimeType::Gif => "image/gif".to_owned(),
        MimeType::Unknown(mime) => mime.to_owned(),
        _ => "application/octet-stream".to_owned(),
    }
}

// Returns the last modified date, or the unix epoch on failure
pub fn get_last_modified_date(meta: Result<Metadata, Error>) -> PrimitiveDateTime {
    match meta {
        Ok(file_meta) => match file_meta.modified() {
           Ok(file_date_modified) => system_time_to_primitive_datetime(file_date_modified),
           Err(_) => system_time_to_primitive_datetime(UNIX_EPOCH) 
        },
        Err(_) => system_time_to_primitive_datetime(UNIX_EPOCH) 
    } 
}

pub fn system_time_to_primitive_datetime(system_time: SystemTime) -> PrimitiveDateTime {
    let dt: OffsetDateTime = system_time.into();
    PrimitiveDateTime::new(dt.date(), dt.time())
}

