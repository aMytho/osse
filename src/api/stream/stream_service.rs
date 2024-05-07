use std::{io::{self, Error}, ops::Range};

// Helper function to parse range header value
pub fn parse_range(range_header: String) -> Result<Range<u64>, Error> {
    // Parse the range header value, assuming format like "bytes=start-end"
    // For simplicity, this example only handles the "bytes=" prefix
    let range_str = range_header.trim_start_matches("bytes=");
    let mut parts = range_str.split('-');

    println!("{:?}", parts);

    let start_str = parts.next().ok_or(io::ErrorKind::InvalidData)?;
    let end_str = parts.next().ok_or(io::ErrorKind::InvalidData)?;
    let start = match start_str.parse() {
        Ok(val) => val,
        Err(_err) => return Err(io::ErrorKind::InvalidData.into())
    };
    let end:u64 = match end_str.parse() {
        Ok(val) => val,
        Err(_err) => return Err(io::ErrorKind::InvalidData.into())
    };

    println!("Range is valid {:?}", start..(end + 1));
    Ok(start..(end + 1))
}