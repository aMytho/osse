use poem::{handler, IntoResponse};
use poem::http::StatusCode;
use poem::web::{Data, Json, Path};
use poem::Error;
use crate::api::playlists::playlist_service::PlaylistService;
use crate::api::shared::dto::GetByName;
use crate::entities::playlist::Playlist;
use crate::AppState;
use super::dto::CreatePlaylist;

#[handler]
pub async fn get_all_playlists(state: Data<&AppState>) -> Json<Vec<Playlist>> {
    let playlist_service = PlaylistService::new(state.db.clone());
    Json(playlist_service.get_all().await)
}

#[handler]
pub async fn get_playlist(
    state: Data<&AppState>,
    Path(playlist_id): Path<i32>
) -> Result<impl IntoResponse, Error> {
    let playlist_service = PlaylistService::new(state.db.clone());
    match playlist_service.get_playlist_by_id(playlist_id).await {
        Some(a) => Ok(Json(a)),
        None => Err(Error::from_string("No Playlist", StatusCode::NOT_FOUND))
    }
}

#[handler]
pub async fn create_playlist(
    state: Data<&AppState>,
    Json(req): Json<GetByName>)
-> Result<impl IntoResponse, Error> {

    let playlist_service = PlaylistService::new(state.db.clone());
    match playlist_service.create_playlist(req.name).await {
        Ok(_c) => Ok(()),
        Err(_e) => Err(Error::from_string("Failed to create playlist", StatusCode::INTERNAL_SERVER_ERROR))
    }
}

#[handler]
pub async fn add_track_to_playlist(
    state: Data<&AppState>,
    Json(req): Json<CreatePlaylist>
) -> Result<impl IntoResponse, Error>{
    let playlist_service = PlaylistService::new(state.db.clone());
    match playlist_service.add_track_to_playlist(req.track_id, req.playlist_id) {
        Ok(_) => Ok(()),
        Err(_) => Err(Error::from_string("Failed to add track to playlist", StatusCode::INTERNAL_SERVER_ERROR))
    }
}

#[handler]
pub async fn get_playlist_tracks(
    state: Data<&AppState>,
    Path(id): Path<i32>
) -> Result<impl IntoResponse, Error> {
    let playlist_service = PlaylistService::new(state.db.clone());
    match playlist_service.playlist_tracks(id) {
       Ok(t) => Ok(Json(t)),
       Err(e) => {
            println!("{}", e);
            Err(Error::from_string("Playlist not found", StatusCode::NOT_FOUND))
       }
   }
}
