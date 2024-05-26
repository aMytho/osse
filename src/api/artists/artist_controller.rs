use poem::{handler, IntoResponse};
use poem::http::StatusCode;
use poem::web::{Json, Query};
use poem::Error;
use sea_orm::EntityTrait;
use crate::entities::artist::Model;
use crate::global;
use crate::entities::prelude::Artist;

use super::dto::GetArtistByid;

#[handler]
pub async fn get_all_artists() -> Json<Vec<Model>> {
    let db = global::get_db().await;

    match Artist::find().all(&db.clone()).await {
        Ok(artists) => Json(artists),
        Err(_err) => Json(vec![])
    }
}

#[handler]
pub async fn get_artist(Query(GetArtistByid {id}): Query<GetArtistByid>) -> Result<impl IntoResponse, Error> {
    let db = global::get_db().await;
    match Artist::find_by_id(id).one(&db).await {
        Ok(artist) => {
            match artist {
                Some(a) => Ok(Json(a)),
                None => Err(Error::from_string("No Artist", StatusCode::NOT_FOUND))
            }
        },
        Err(_) => Err(Error::from_string("DB Error", StatusCode::INTERNAL_SERVER_ERROR))
    }
}