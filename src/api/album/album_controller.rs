use crate::{api::shared::dto::GetById, entities::{album::Model, prelude::Album}, AppState};
use poem::{handler, http::StatusCode, web::{Data, Json, Query}, Error, IntoResponse};
use sea_orm::EntityTrait;

use super::album_service::AlbumService;


#[handler]
pub async fn get_all_albums(state: Data<&AppState>) -> Json<Vec<Model>> {
    match Album::find().all(&state.db).await {
        Ok(tracks) => Json(tracks),
        Err(_err) => Json(vec![])
    }
}

#[handler]
pub async fn get_album(state: Data<&AppState>, query: Query<GetById>) -> Result<impl IntoResponse, Error> {
    let album_service = AlbumService::new(&state.db);
    match album_service.get_album_by_id(query.id).await {
        Some(album) => Ok(Json(album)),
        None => Err(Error::from_status(StatusCode::NOT_FOUND))
    }
}
