use crate::{api::tracks::dto::{TrackSearch, TrackSearchResult}, entities::{track::Track, util::Pagination}, metadata, AppState};
use poem::{handler, http::StatusCode, web::{Data, Json, Query}, Error, IntoResponse, Response};

use super::{dto::GetCoverArtById, track_service::TrackService};

#[handler]
pub fn get_all_tracks(state: Data<&AppState>) -> Json<Vec<Track>> {
    Json(TrackService::new(state.db.clone()).get_all_tracks())
}

#[handler]
pub fn get_cover_art_for_track(state: Data<&AppState>, Query(GetCoverArtById {id}): Query<GetCoverArtById>) -> Result<impl IntoResponse, Error> {
    match TrackService::new(state.db.clone()).get_track_by_id(id) {
        Some(track) => {
            match metadata::get_cover_art(track.location) {
                Some(data) => Ok(Response::builder()
                    .content_type(data.mime_type)
                    .body(data.data.to_vec()
                )),
                None => Err(Error::from_string("No Metadata", StatusCode::NOT_FOUND))
            }
        },
        None => Err(Error::from_string("No Metadata", StatusCode::NOT_FOUND))
    }
}

#[handler]
pub fn search_for_track(
    state: Data<&AppState>,
    Query(query): Query<TrackSearch>
) -> Json<TrackSearchResult> {
    let track_service = TrackService::new(state.db.clone());

    let mut tracks: Vec<Track> = if let (Some(track), Some(offset)) = (query.track, query.track_offset) {
        track_service.get_tracks_by_name(track, Pagination::new(offset, 25))
    } else {
        track_service.get_tracks(Pagination::new(query.track_offset.unwrap_or(0), 25))
    };
    
    Json(TrackSearchResult {
        tracks
    })
}

#[handler]
pub async fn scan(state: Data<&AppState>) -> &'static str {
    TrackService::new(state.db.clone()).scan_files(&state.config.files).await;
    "Scan Complete!"
}