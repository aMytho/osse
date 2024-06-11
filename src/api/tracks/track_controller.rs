use crate::{entities::{prelude::*, track::Model}, global::{self, CONFIG}, metadata};
use poem::{handler, http::{header, StatusCode}, web::{Json, Query}, Error, IntoResponse, Response};
use sea_orm::EntityTrait;

use super::{dto::GetCoverArtById, track_service::scan_files};

#[handler]
pub async fn get_all_tracks() -> Json<Vec<Model>> {
    let db = global::get_db().await;

    match Track::find().all(&db.clone()).await {
        Ok(tracks) => Json(tracks),
        Err(_err) => Json(vec![])
    }
}

#[handler]
pub async fn get_cover_art_for_track(Query(GetCoverArtById {id}): Query<GetCoverArtById>) -> Result<impl IntoResponse, Error>{
    let db = global::get_db().await;

    match Track::find_by_id::<i32>(id.into()).one(&db).await {
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
pub async fn scan() -> &'static str {
    scan_files(CONFIG.files.clone()).await;
    "Scan Complete!"
}