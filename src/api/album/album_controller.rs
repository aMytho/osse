use crate::{entities::{album::Model, prelude::Album}, AppState};
use poem::{handler, web::{Data, Json}};
use sea_orm::EntityTrait;


#[handler]
pub async fn get_all_albums(state: Data<&AppState>) -> Json<Vec<Model>> {
    match Album::find().all(&state.db).await {
        Ok(tracks) => Json(tracks),
        Err(_err) => Json(vec![])
    }
}
