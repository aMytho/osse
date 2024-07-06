use crate::{
    api::{
        album::dto::{AlbumResponse, AllAlbumsQuery, Dto},
        shared::dto::GetById,
    },
    AppState,
};
use poem::{
    handler,
    http::StatusCode,
    web::{Data, Json, Query},
    Error, IntoResponse,
};

use super::album_service::AlbumService;

#[handler]
pub async fn get_all_albums(
    state: Data<&AppState>,
    query: Query<AllAlbumsQuery>,
) -> Json<Vec<AlbumResponse>> {
    let album_service = AlbumService::new(state.db.clone());
    match &query.tracks {
        Some(_req) => Json(album_service.get_all_with_tracks().await.to_models()),
        None => Json(album_service.get_all().await.to_models()),
    }
}

#[handler]
pub async fn get_album(
    state: Data<&AppState>,
    query: Query<GetById>,
) -> Result<impl IntoResponse, Error> {
    let album_service = AlbumService::new(state.db.clone());
    match album_service.get_album_by_id(query.id).await {
        Some(album) => Ok(Json(album)),
        None => Err(Error::from_status(StatusCode::NOT_FOUND)),
    }
}
