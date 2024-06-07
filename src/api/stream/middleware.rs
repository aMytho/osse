use poem::{http::StatusCode, Endpoint, Request};
use super::stream_service::parse_range;

const RANGE_HEADER: &str = "Range";

#[derive(Clone, Debug)]
pub struct RangeMiddleware {
    pub start: u64,
    pub end: Option<u64>
}

pub async fn validate_range<E: Endpoint>(next: E, mut req: Request) -> Result<E::Output, poem::Error> {
    if let Some(value) = req
        .headers()
        .get(RANGE_HEADER)
        .and_then(|value| value.to_str().ok())
    {
        // Insert range in request
        let range = value.to_string();
        match parse_range(range) {
            Ok(range) => {
                req.extensions_mut().insert(range);
            },
            Err(_) => return Err(poem::Error::from_status(StatusCode::BAD_REQUEST))
        }

        // call the next endpoint
        next.call(req).await
    } else {
        Err(poem::Error::from_status(StatusCode::BAD_REQUEST))
    }
}