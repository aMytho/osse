use poem::{error::IntoResult, http::{header::CACHE_CONTROL, StatusCode}, web::WithHeader, Endpoint, Error, IntoResponse, Request, Result};
use serde::Deserialize;

use crate::{api::tracks::track_service::TrackService, entities::track::Track, AppState};

const TRACK_HEADER: &str = "Track";

#[derive(Clone)]
pub struct TrackMiddleware(pub Track);

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
    let track_service = TrackService::new(state.db.clone());

    match header.unwrap().parse::<i32>() {
        Ok(id) => {
            let track = track_service.get_track_by_id(id);
            if let Some(track) = track {
                req.extensions_mut().insert(TrackMiddleware(track));
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
) -> Result<E::Output> {
    let query = req.params::<TrackId>();
    if let Ok(id) = query {
        let state: &AppState = req.data().unwrap();
        let track_service = TrackService::new(state.db.clone());

        let track = track_service.get_track_by_id(id.id);
        if let Some(track) = track {
            req.extensions_mut().insert(TrackMiddleware(track));
            return next.call(req).await
        } else {
            return Err(Error::from_status(StatusCode::NOT_FOUND));
        }
    }

    return Err(Error::from_status(StatusCode::BAD_REQUEST));
}

pub async fn cache_control<E: Endpoint>(
    next: E,
    req: Request,
) -> Result<WithHeader<<E as Endpoint>::Output>>  {
    let result = next.call(req).await?;
    result
        .with_header(CACHE_CONTROL, "public, max-age=3600".parse::<String>().unwrap())
        .into_result()
}
