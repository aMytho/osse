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

    /**
     * Creates albums and returns the ID of the last album inserted.
     * Names is a vec of tuples where the first item is the album name and second is artist id (nullable)
     */
    pub async fn create_albums(&self, names: &Vec<(String, Option<i32>)>) -> i32 {
        Album::insert_many(names.iter().map(|f| ActiveModel {
            name: sea_orm::ActiveValue::Set(f.0.to_string()),
            artist_id: sea_orm::ActiveValue::Set(f.1),
            ..Default::default()
        })).exec(self.db).await.unwrap().last_insert_id
    }
}
