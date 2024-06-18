use crate::{entities::{prelude::*, track::Model}, metadata, AppState};
use poem::{handler, http::{header, StatusCode}, web::{Data, Json, Query}, Error, IntoResponse, Response};
use sea_orm::EntityTrait;

use super::{dto::GetCoverArtById, track_service::TrackService};

#[handler]
pub async fn get_all_tracks(state: Data<&AppState>) -> Json<Vec<Model>> {
    Json(TrackService::new(&state.db).get_all_tracks().await)
}

#[handler]
pub async fn get_cover_art_for_track(state: Data<&AppState>, Query(GetCoverArtById {id}): Query<GetCoverArtById>) -> Result<impl IntoResponse, Error>{
    match Track::find_by_id::<i32>(id.into()).one(&state.db).await {
        Ok(track) => {
            match track {
                Some(track) => {
                    match metadata::get_cover_art(track.location) {
                        Some(data) => Ok(Response::builder()
                            .header(header::CACHE_CONTROL, "max-age=31536000")
                            // to-do: Add last modified and etag information to update if file has been changed
                            .content_type(data.mime_type)
                            .body(data.data.to_vec()
                        )),
                        None => Err(Error::from_string("No Metadata", StatusCode::NOT_FOUND))
                    }

                },
                None => Err(Error::from_string("No Metadata", StatusCode::NOT_FOUND)),
            }
        },
        Err(_) => Err(Error::from_string("No Metadata", StatusCode::NOT_FOUND))
    }
}

#[handler]
pub async fn scan(state: Data<&AppState>) -> &'static str {
    TrackService::new(&state.db).scan_files(&state.config.files).await;
    "Scan Complete!"
}