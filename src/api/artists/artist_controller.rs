use poem::{handler, IntoResponse};
use poem::http::StatusCode;
use poem::web::{Data, Json, Query};
use poem::Error;
use crate::api::artists::artist_service::ArtistService;
use crate::entities::artist::Artist;
use crate::AppState;

use super::dto::GetArtistByid;

#[handler]
pub async fn get_all_artists(state: Data<&AppState>) -> Json<Vec<Artist>> {
    let artist_service = ArtistService::new(state.db.clone());
    Json(artist_service.get_all().await)
}

#[handler]
pub async fn get_artist(
    state: Data<&AppState>,
    Query(GetArtistByid {id}): Query<GetArtistByid>
) -> Result<impl IntoResponse, Error> {
    let artist_service = ArtistService::new(state.db.clone());
    match artist_service.get_artist_by_id(id).await {
        Some(a) => Ok(Json(a)),
        None => Err(Error::from_string("No Artist", StatusCode::NOT_FOUND))
    }
}
