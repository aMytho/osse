use poem::{http::StatusCode, Endpoint, Error, Request};
use sea_orm::EntityTrait;
use serde::Deserialize;

use crate::{entities::{prelude::Track, track::Model}, AppState};

const TRACK_HEADER: &str = "Track";

#[derive(Clone)]
pub struct TrackMiddleware(pub Model);

#[derive(Deserialize)]
pub struct TrackId {
    id: i32
}

/**
 * Checks the header for a track ID and adds it to the request.
 */
pub async fn validate_track_header<E: Endpoint>(
    next: E,
    mut req: Request,
) -> Result<E::Output, poem::Error> {
    let header = req.header(TRACK_HEADER);

    if let None = header {
        return Err(Error::from_status(StatusCode::BAD_REQUEST));
    };

    let state: &AppState = req.data().unwrap();

    match header.unwrap().parse::<i32>() {
        Ok(id) => {
            let track = Track::find_by_id(id).one(&state.db).await;
            if let Ok(track) = track {
                if let Some(track) = track {
                    req.extensions_mut().insert(TrackMiddleware(track));
                } else {
                    return Err(Error::from_status(StatusCode::NOT_FOUND));
                }
            } else {
                return Err(Error::from_status(StatusCode::NOT_FOUND));
            }
        }
        Err(_) => return Err(Error::from_status(StatusCode::BAD_REQUEST)),
    }

    next.call(req).await
}

pub async fn validate_track_query<E: Endpoint>(
    next: E,
    mut req: Request,
) -> Result<E::Output, poem::Error> {
    let query = req.params::<TrackId>();
    if let Ok(id) = query {
        let state: &AppState = req.data().unwrap();

        let track = Track::find_by_id(id.id).one(&state.db).await;
        if let Ok(track) = track {
            if let Some(track) = track {
                req.extensions_mut().insert(TrackMiddleware(track));
                return next.call(req).await
            } else {
                return Err(Error::from_status(StatusCode::NOT_FOUND));
            }
        } else {
            return Err(Error::from_status(StatusCode::NOT_FOUND));
        }
    }

    return Err(Error::from_status(StatusCode::BAD_REQUEST));
}
