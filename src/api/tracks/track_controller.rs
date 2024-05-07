use crate::{entities::{prelude::*, track::Model}, global::{self, CONFIG}};
use poem::{handler, http::StatusCode, web::{Json, Query}, Error, IntoResponse, Response};
use sea_orm::EntityTrait;

use super::{dto::GetCoverArtById, track_service::{scan_files, get_cover_art}};

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
    println!("The id is {}", id);
    let db = global::get_db().await;

    match Track::find_by_id::<i32>(id.into()).one(&db).await {
        Ok(track) => {
            match track {
                Some(track) => {
                    match get_cover_art(track) {
                        Some(data) => Ok(Response::builder().content_type("image/png").body(data)),
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