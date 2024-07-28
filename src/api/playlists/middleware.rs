use poem::{http::StatusCode, Body, Endpoint, Error, Request, Result};

use crate::AppState;

use super::{dto::CreatePlaylist, playlist_service::PlaylistService};

pub async fn valid_playlist<E: Endpoint>(next: E, req: Request) -> Result<E::Output> {
    // Extract the body from the request
    let parts = req.into_parts();
    let body = parts.1
        .into_vec()
        .await?;
    let body_str = String::from_utf8(body).unwrap();

    // Get the playlist from the body
    let data: CreatePlaylist = match serde_json::from_str(&body_str) {
        Ok(d) => d,
        Err(_e) => return Err(Error::from_string("You must provide a playlist_id and a track_id", StatusCode::BAD_REQUEST))
    };

    // Check that the playlist and track exist, and that they are not yet linked
    let state: &AppState = parts.0.extensions.get()
        .ok_or(Error::from_status(StatusCode::INTERNAL_SERVER_ERROR))?;

    let playlist_service = PlaylistService::new(state.db.clone());

    let playlist_exists = playlist_service.get_playlist_by_id(data.playlist_id).await;
    let tracks = playlist_service.playlist_tracks(data.playlist_id);

    if let (Some(_), Ok(tracks)) = (playlist_exists, tracks) {
        // Check that they are not linked
        if tracks.into_iter().any(|f| f.id == data.track_id) {
            return Err(Error::from_string("That track is already in the playlist", StatusCode::BAD_REQUEST));
        }

        let req = Request::from_parts(parts.0, Body::from_string(body_str));
        next.call(req).await
    } else {
        Err(Error::from_status(StatusCode::NOT_FOUND))
    }
}

