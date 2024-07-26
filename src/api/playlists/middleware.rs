use poem::{http::StatusCode, Body, Endpoint, Error, Request, Result};

use crate::{api::{playlists::playlist_service, tracks::track_service::{self, TrackService}}, AppState};

use super::{dto::CreatePlaylist, playlist_service::PlaylistService};

pub async fn valid_playlist<E: Endpoint>(next: E, mut req: Request) -> Result<E::Output> {
    // Extract the body from the request
    let parts = req.into_parts();
    let body = parts.1
        .into_vec()
        .await?;
    let body_str = String::from_utf8(body).unwrap();

    // Get the playlist from the body
    let data: CreatePlaylist = match serde_json::from_str(&body_str) {
        Ok(d) => d,
        Err(e) => return Err(Error::from_status(StatusCode::BAD_REQUEST))
    };

    // Check that the playlist and track exist, and that they are not yet linked
    let state: &AppState = parts.0.extensions.get()
        .ok_or(Error::from_status(StatusCode::INTERNAL_SERVER_ERROR))?;

    let playlist_service = PlaylistService::new(state.db.clone());
    let track_service = TrackService::new(state.db.clone());

    let playlist_exists = playlist_service.get_playlist_by_id(data.playlist_id).await;
    let track_exists = track_service.get_track_by_id(data.track_id);

    if let (Some(_), Some(_)) = (playlist_exists, track_exists) {
        // Check that they are not linked
        
    }

    let req = Request::from_parts(parts.0, Body::from_string(body_str));
    next.call(req).await
}