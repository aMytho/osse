use diesel::{associations::HasTable, insert_into, r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, QueryDsl, RunQueryDsl, SelectableHelper, SqliteConnection};
use crate::{api::{album::album_service, shared::service::DbConn}, entities::track::TrackForm, schema::tracks::dsl::*};

use crate::api::artists::artist_service;
use crate::{entities::track::Track, files, metadata};

pub struct TrackService {
    pub db: Pool<ConnectionManager<SqliteConnection>>
}

impl TrackService {
    pub fn new(db: Pool<ConnectionManager<SqliteConnection>>) -> TrackService {
        TrackService {db}
    }

    pub fn get_all_tracks(&self) -> Vec<Track> {
        tracks
            .select(Track::as_select())
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }

    pub fn get_track_by_id(&self, track_id: i32) -> Option<Track> {
        tracks
            .select(Track::as_select())
            .filter(id.eq(track_id))
            .first(&mut self.conn())
            .ok()
    }

    pub async fn scan_files(&self, files: &Vec<String>) {
        // For each directory, scan each file
        let artist_service = artist_service::ArtistService::new(self.db.clone());
        let album_service = album_service::AlbumService::new(self.db.clone());
        for dir in files {
            let files = match files::load_directory(dir) {
                Ok(files) => files,
                Err(_err) => panic!("Failed to load dir: {dir}")
            };
    
            // Get the file metadata (tags/meta)
            let files = metadata::scan_files(files, &artist_service, &album_service)
                .await
                .iter()
                .map(|f| TrackForm::from(f))
                .collect::<Vec<_>>();
            let _ = insert_into(tracks)
                .values(&files)
                .execute(&mut self.conn());
        }
    }

    pub fn count(&self) -> Option<i64> {
        tracks::table()
            .count()
            .get_result(&mut self.conn())
            .ok()
    }
}

impl DbConn for TrackService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}