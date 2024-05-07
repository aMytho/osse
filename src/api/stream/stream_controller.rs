use std::fs::File;
use std::io::{Read, Seek, SeekFrom};

use poem::http::{header, StatusCode};
use poem::web::Data;
use poem::{handler, Error, IntoResponse, Response};

use crate::api::shared::middleware::TrackMiddleware;
use crate::api::stream::middleware::RangeMiddleware;


#[handler]
pub fn stream_file(
    Data(range): Data<&RangeMiddleware>,
    Data(track): Data<&TrackMiddleware>
) -> Result<impl IntoResponse, Error> {
    let range = &range.0;

    // Parse the range header value to get the range requested by the client
    // For simplicity, this example assumes a single range is requested
    println!("{:?}", range);

    // Open the audio file
    if let Ok(mut file) = File::open(track.0.location.to_owned()) {
        // Seek to the requested range
        if let Err(_) = file.seek(SeekFrom::Start(range.start)) {
            return Err(poem::Error::from_string(
                "Can't seek",
                StatusCode::BAD_REQUEST,
            ));
        }

        // Read the requested range into a buffer
        let mut buffer = vec![0; (range.end - range.start) as usize];
        if let Err(_) = file.read_exact(&mut buffer) {
            return Err(poem::Error::from_string("EOF", StatusCode::BAD_REQUEST));
        }

        // Return the requested range with the appropriate headers
        return Ok(Response::builder()
            .header(header::CONTENT_TYPE, "audio/mpeg")
            .body(buffer));
    }

    // If no range request or an error occurred, return the entire file
    return Err(poem::Error::from_string(
        "Can't seek",
        StatusCode::BAD_REQUEST,
    ));
}
