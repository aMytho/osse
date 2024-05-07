use poem::{http::StatusCode, Endpoint, Error, Request};
use sea_orm::EntityTrait;

use crate::{entities::{prelude::*, track::Model}, global};

const TRACK_HEADER: &str = "Track";

#[derive(Clone)]
pub struct TrackMiddleware(pub Model);

/**
 * Checks the header for a track ID and adds it to the request.
 */
pub async fn validate_track_header<E: Endpoint>(next: E, mut req: Request) -> Result<E::Output, poem::Error> {
    let header = req.header(TRACK_HEADER);

    if let None = header {
        return Err(Error::from_status(StatusCode::BAD_REQUEST));
    };

    match header.unwrap().parse::<i32>() {
        Ok(id) => {
            let db = global::get_db().await;

            let track = Track::find_by_id(id).one(&db).await;
            if let Ok(track) = track {
                if let Some(track) = track {
                    req.extensions_mut().insert(TrackMiddleware(track));
                } else {
                    return Err(Error::from_status(StatusCode::NOT_FOUND));
                }
            } else {
                return Err(Error::from_status(StatusCode::NOT_FOUND));
            }
        },
        Err(_) => return Err(Error::from_status(StatusCode::BAD_REQUEST))
    }
    
    next.call(req).await
}