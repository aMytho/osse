use crate::{entities::{album::Model, prelude::Album}, global};
use poem::{handler, web::Json};
use sea_orm::EntityTrait;


#[handler]
pub async fn get_all_albums() -> Json<Vec<Model>> {
    let db = global::get_db().await;

    match Album::find().all(&db.clone()).await {
        Ok(tracks) => Json(tracks),
        Err(_err) => Json(vec![])
    }
}
