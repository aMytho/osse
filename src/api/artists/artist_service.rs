use sea_orm::{ActiveModelTrait, ColumnTrait, DbErr, EntityTrait, QueryFilter};

use crate::{entities::{artist::{self, Model}, prelude::Artist}, global};


pub async fn get_artist_by_name(name: String) -> Option<Model> {
    let db = global::get_db().await;

    let artist = Artist::find().filter(artist::Column::Name.eq(name)).one(&db).await;
    if let Ok(artist) = artist {
        match artist {
            Some(artist) => Some(artist),
            None => None
        }
    } else {
        None
    }
}

pub async fn create_artist(name: String) -> Result<Model, DbErr> {
    let db = global::get_db().await;

    let model = artist::ActiveModel {
        name: sea_orm::ActiveValue::Set(name),
        ..Default::default()
    };

    model.insert(&db).await
}