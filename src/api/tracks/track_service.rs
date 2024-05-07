use sea_orm::EntityTrait;
use crate::{entities::track::{self}, files, global, metadata};

pub async fn scan_files(files: Vec<String>) {
    let db = global::get_db().await;
    
    // For each directory, scan each file
    for dir in files {
        let files = match files::load_directory(dir.clone()) {
            Ok(files) => files,
            Err(_err) => panic!("Failed to load dir: {dir}")
        };

        // Get the file metadata (tags/meta)
        let files = metadata::scan_files(files).await.into_iter().map(|f| {
            track::ActiveModel {
                location: sea_orm::ActiveValue::Set(f.path),
                title: sea_orm::ActiveValue::Set(f.title.unwrap()),
                updated_at: sea_orm::ActiveValue::Set(f.updated_at.unwrap()),
                artist_id: sea_orm::ActiveValue::Set(f.artist),
                ..Default::default()
            }
        });

        let _ = track::Entity::insert_many(files).exec(&db).await;
    }
}

pub fn get_cover_art(track: track::Model) -> Option<Vec<u8>> {
    // Get art for the file, falls back to dir
    match metadata::get_cover_art(track.location) {
        Some(cover) => Some((*cover.data).to_vec()),
        None => None
    }
}
