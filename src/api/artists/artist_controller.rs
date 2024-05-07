use poem::handler;
use poem::web::Json;
use sea_orm::EntityTrait;
use crate::entities::artist::Model;
use crate::global;
use crate::entities::prelude::Artist;

#[handler]
pub async fn get_all_artists() -> Json<Vec<Model>> {
    let db = global::get_db().await;

    match Artist::find().all(&db.clone()).await {
        Ok(artists) => Json(artists),
        Err(_err) => Json(vec![])
    }
}