use sea_orm::{ColumnTrait, DatabaseConnection, EntityTrait, QueryFilter};

use crate::entities::{
    album::{self, ActiveModel, Model},
    prelude::Album,
};

pub struct AlbumService<'a> {
    pub db: &'a DatabaseConnection,
}

impl AlbumService<'_> {
    pub fn new(db: &DatabaseConnection) -> AlbumService {
        AlbumService { db }
    }

    pub async fn get_album_by_id(&self, id: i32) -> Option<Model> {
        Album::find_by_id(id).one(self.db).await.ok()?
    }

    pub async fn get_albums_by_name(&self, names: Vec<String>) -> Vec<Model> {
        match Album::find()
            .filter(album::Column::Name.is_in(names))
            .all(self.db)
            .await
        {
            Ok(result) => result,
            Err(_) => Vec::new(),
        }
    }

    pub async fn get_albums_with_id_less_than(&self, id: i32) -> Vec<Model> {
        match Album::find()
            .filter(album::Column::Id.lte(id))
            .all(self.db)
            .await
        {
            Ok(result) => result,
            Err(_) => Vec::new(),
        }
    }

    /**
     * Creates albums and returns the ID of the last album inserted
     */
    pub async fn create_albums(&self, names: &Vec<String>) -> i32 {
        Album::insert_many(names.iter().map(|f| ActiveModel {
            name: sea_orm::ActiveValue::Set(f.to_string()),
            artist_id: sea_orm::ActiveValue::NotSet,
            ..Default::default()
        })).exec(self.db).await.unwrap().last_insert_id
    }
}
