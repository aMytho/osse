use std::io::{self, Error};

use poem::http::{header::USER_AGENT, HeaderMap};

use crate::api::shared::middleware::TrackMiddleware;

use super::middleware::RangeMiddleware;

/**
 * 5 MB buffer
 */
const BUFFER_AMOUNT: usize = 5242880;

// Helper function to parse range header value
pub fn parse_range(range_header: String) -> Result<RangeMiddleware, Error> {
    // Parse the range header value, assuming format like "bytes=start-end"
    // For simplicity, this example only handles the "bytes=" prefix
    let range_str = range_header.trim_start_matches("bytes=");
    let mut parts = range_str.split('-');

    println!("{:?}", parts);

    let start_str = parts.next().ok_or(io::ErrorKind::InvalidData)?;
    let end_str = parts.next().unwrap_or("");
    let start = match start_str.parse() {
        Ok(val) => val,
        Err(_err) => return Err(io::ErrorKind::InvalidData.into())
    };

    match end_str.parse::<u64>() {
        Ok(val) => return Ok(RangeMiddleware {start, end: Some(val + 1)}),
        Err(_err) => return Ok(RangeMiddleware {start, end: None})
    };
}

/**
 * Guesses the mime type based on the filename
 */
pub fn guess_mime_type(name: &String) -> &str {
    if name.ends_with("mp3") {
        "audio/mpeg"
    } else if name.ends_with("wav") {
        "audio/wav"
    } else {
        "application/octet-stream"
    }
}

pub fn buffer_for_track_range(range: &RangeMiddleware, header_map: &HeaderMap, track: &TrackMiddleware) -> (Vec<u8>, String) {
    // If they have a range ending point, read to that point
    if let Some(end) = range.end {
        let buffer = vec![0; (end - range.start) as usize];
        let content_range = format!("bytes {}-{}/{}", range.start, end - 1, track.0.size);
        (buffer, content_range)
    } else {
        // No ending point...
        if let Some(agent) = header_map.get(USER_AGENT) {
            if agent.to_str().unwrap().contains("Firefox") {
                // Return the whole fire since firefox doesn't buffer
                let buffer = vec![0; track.0.size as usize - range.start as usize];
                let content_range = format!(
                    "bytes {}-{}/{}",
                    range.start, track.0.size - 1, track.0.size
                );

                return (buffer, content_range);
            }
        }

        // If the buffer is larger than the range, use the buffer
        if track.0.size as usize - range.start as usize > BUFFER_AMOUNT {
            (vec![0; BUFFER_AMOUNT], format!(
                "bytes {}-{}/{}",
                range.start, range.start as usize + BUFFER_AMOUNT, track.0.size)
            )
        } else {
            // The buffer is smaller than the remaining data, use remaining data
            (vec![0; track.0.size as usize - range.start as usize], format!(
                "bytes {}-{}/{}",
                range.start, track.0.size - 1, track.0.size)
            )
        }
    }
}