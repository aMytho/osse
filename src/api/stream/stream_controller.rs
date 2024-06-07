use std::fs::File;
use std::io::{Read, Seek, SeekFrom};

use poem::http::{header, HeaderMap, StatusCode};
use poem::web::Data;
use poem::{handler, Error, IntoResponse, Response};

use crate::api::shared::middleware::TrackMiddleware;
use crate::api::stream::middleware::RangeMiddleware;
use crate::api::stream::stream_service::{buffer_for_track_range, guess_mime_type};


#[handler]
pub fn stream_file(
    Data(range): Data<&RangeMiddleware>,
    Data(track): Data<&TrackMiddleware>,
    header_map: &HeaderMap
) -> Result<impl IntoResponse, Error> {
    // Open the audio file
    if let Ok(mut file) = File::open(track.0.location.to_owned()) {
        let (mut buffer, content_range) = buffer_for_track_range(range, header_map, track);
        // Seek to the requested range
        if let Err(_) = file.seek(SeekFrom::Start(range.start)) {
            return Err(poem::Error::from_string("Can't seek", StatusCode::RANGE_NOT_SATISFIABLE));
        }

        if let Err(_) = file.read_exact(&mut buffer) {
            return Err(poem::Error::from_string("End of file.", StatusCode::RANGE_NOT_SATISFIABLE));
        };

        return Ok(Response::builder()
            .header(header::CONTENT_TYPE, guess_mime_type(&track.0.location))
            .header(header::CONTENT_RANGE, content_range)
            .status(StatusCode::PARTIAL_CONTENT)
            .body(buffer));
    } else {
        return Err(poem::Error::from_string("Failed to read file", StatusCode::NOT_FOUND));
    }
}

#[handler]
pub fn stream_file_header(
    Data(track): Data<&TrackMiddleware>
) -> Response {
    println!("Browser followed spec and requested HTTP Range support.");
    
    Response::builder()
        .header(header::ACCEPT_RANGES, "bytes")
        .header(header::CONTENT_LENGTH, track.0.size)
        .finish()
}