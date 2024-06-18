use sea_orm::{ActiveModelTrait, ColumnTrait, DatabaseConnection, DbErr, EntityTrait, QueryFilter};

use crate::entities::{artist::{self, Model}, prelude::Artist};

pub struct ArtistService<'a> {
    pub db: &'a DatabaseConnection
}

impl ArtistService<'_> {
    pub fn new(db: &DatabaseConnection) -> ArtistService {
        ArtistService {db}
    }

    pub async fn get_artist_by_name(&self, name: String) -> Option<Model> {
        match Artist::find().filter(artist::Column::Name.eq(name)).one(self.db).await {
            Ok(artist) => artist,
            Err(_) => None
        }
    }
    
    pub async fn create_artist(&self, name: String) -> Result<Model, DbErr> {
        let model = artist::ActiveModel {
            name: sea_orm::ActiveValue::Set(name),
            ..Default::default()
        };
    
        model.insert(self.db).await
    }
}
