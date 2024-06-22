use sea_orm::{DatabaseConnection, EntityTrait};
use crate::api::artists::artist_service;
use crate::entities::prelude::Track;
use crate::{entities::track::{self, Model}, files, metadata};

pub struct TrackService<'a> {
    pub db: &'a DatabaseConnection
}

impl TrackService<'_> {
    pub fn new(db: &DatabaseConnection) -> TrackService {
        TrackService {db}
    }

    pub async fn get_all_tracks(&self) -> Vec<Model> {
        match Track::find().all(self.db).await {
            Ok(tracks) => tracks,
            Err(_err) => Vec::new()
        }
    }

    pub async fn scan_files(&self, files: &Vec<String>) {
        // For each directory, scan each file
        let artist_service = artist_service::ArtistService::new(self.db);
        for dir in files {
            let files = match files::load_directory(dir) {
                Ok(files) => files,
                Err(_err) => panic!("Failed to load dir: {dir}")
            };
    
            // Get the file metadata (tags/meta)
            let files = metadata::scan_files(files, &artist_service).await.into_iter().map(|f| {
                track::ActiveModel {
                    location: sea_orm::ActiveValue::Set(f.path),
                    title: sea_orm::ActiveValue::Set(f.title.unwrap()),
                    updated_at: sea_orm::ActiveValue::Set(f.updated_at.unwrap()),
                    artist_id: sea_orm::ActiveValue::Set(f.artist),
                    size: sea_orm::ActiveValue::Set(f.size as i64),
                    bitrate: sea_orm::ActiveValue::Set(f.bitrate),
                    duration: sea_orm::ActiveValue::Set(f.duration as i32),
                    album_id: sea_orm::ActiveValue::Set(f.album_id),
                    ..Default::default()
                }
            });
    
            let _ = track::Entity::insert_many(files).exec(self.db).await;
        }
    }
}
