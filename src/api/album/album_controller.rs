use crate::{
    api::{
        album::dto::{AlbumResponse, AllAlbumsQuery, AllAlbumsResponse},
        shared::dto::GetById,
    },
    entities::{album::Model, prelude::Album, track::Entity as Track},
    AppState,
};
use poem::{
    handler,
    http::StatusCode,
    web::{Data, Json, Query},
    Error, IntoResponse,
};
use sea_orm::EntityTrait;

use super::album_service::AlbumService;

#[handler]
pub async fn get_all_albums(
    state: Data<&AppState>,
    query: Query<AllAlbumsQuery>,
) -> Json<AllAlbumsResponse> {
    match &query.tracks {
        Some(req) => 
            match Album::find().find_with_related(Track).all(&state.db).await {
                Ok(tracks) => {
                    Json(AllAlbumsResponse(tracks.into_iter().map(|f| AlbumResponse {
                            artist_id: f.0.artist_id,
                            id: f.0.id,
                            name: f.0.name,
                            tracks: Some(f.1)
                        }
                    ).collect()))
                },
                Err(_err) => Json(AllAlbumsResponse(vec![])),
            },
        None => match Album::find().all(&state.db).await {
            Ok(tracks) => Json(AllAlbumsResponse(tracks.into_iter().map(|f| AlbumResponse {
                artist_id: f.artist_id,
                id: f.id,
                name: f.name,
                tracks: None
            }).collect())),
            Err(_err) => Json(AllAlbumsResponse(vec![])),
        },
    }
}

#[handler]
pub async fn get_album(
    state: Data<&AppState>,
    query: Query<GetById>,
) -> Result<impl IntoResponse, Error> {
    let album_service = AlbumService::new(&state.db);
    match album_service.get_album_by_id(query.id).await {
        Some(album) => Ok(Json(album)),
        None => Err(Error::from_status(StatusCode::NOT_FOUND)),
    }
}
