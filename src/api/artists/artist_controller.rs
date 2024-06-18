use poem::{handler, IntoResponse};
use poem::http::StatusCode;
use poem::web::{Data, Json, Query};
use poem::Error;
use sea_orm::EntityTrait;
use crate::entities::artist::Model;
use crate::entities::prelude::Artist;
use crate::AppState;

use super::dto::GetArtistByid;

#[handler]
pub async fn get_all_artists(state: Data<&AppState>) -> Json<Vec<Model>> {
    match Artist::find().all(&state.db).await {
        Ok(artists) => Json(artists),
        Err(_err) => Json(vec![])
    }
}

#[handler]
pub async fn get_artist(
    state: Data<&AppState>,
    Query(GetArtistByid {id}): Query<GetArtistByid>
) -> Result<impl IntoResponse, Error> {
    match Artist::find_by_id(id).one(&state.db).await {
        Ok(artist) => {
            match artist {
                Some(a) => Ok(Json(a)),
                None => Err(Error::from_string("No Artist", StatusCode::NOT_FOUND))
            }
        },
        Err(_) => Err(Error::from_string("DB Error", StatusCode::INTERNAL_SERVER_ERROR))
    }
}