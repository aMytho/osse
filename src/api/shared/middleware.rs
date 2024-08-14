use poem::{error::IntoResult, http::{header::CACHE_CONTROL, StatusCode}, web::WithHeader, Endpoint, Error, IntoResponse, Request, Result};
use serde::Deserialize;

use crate::{api::tracks::track_service::TrackService, entities::track::Track, AppState};

#[derive(Clone)]
pub struct TrackMiddleware(pub Track);

#[derive(Deserialize)]
pub struct TrackId {
    id: i32
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
