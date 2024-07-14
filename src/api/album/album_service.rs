use diesel::{associations::HasTable, insert_into, r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, QueryDsl, RunQueryDsl, SelectableHelper, SqliteConnection};
use crate::{api::shared::service::DbConn, entities::{album::Album, track::Track}, schema::tracks};
use crate::schema::albums::dsl::*;
use crate::api::album::dto::Dto;

use super::dto::AlbumResponse;

pub struct AlbumService {
    pub db: Pool<ConnectionManager<SqliteConnection>>
}

impl AlbumService {
    pub fn new(db: Pool<ConnectionManager<SqliteConnection>>) -> AlbumService {
        AlbumService { db }
    }

    pub async fn get_album_by_id(&self, album_id: i32) -> Option<Album> {
        albums
            .select(Album::as_select())
            .filter(id.eq(album_id))
            .first(&mut self.conn())
            .ok()
    }

    pub async fn get_albums_by_name(&self, names: Vec<String>) -> Vec<Album> {
        albums
            .select(Album::as_select())
            .filter(name.eq_any(names))
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }

    pub async fn get_all(&self) -> Vec<Album> {
        albums
            .select(Album::as_select())
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }

    pub async fn get_all_with_tracks(&self) -> Vec<(Album, Track)> {
        albums::table()
            .inner_join(tracks::table)
            .select((Album::as_select(), Track::as_select()))
            .load::<(Album, Track)>(&mut self.conn())
            .unwrap_or(Vec::new())
    }

    pub fn get_album_with_tracks(&self, album_id: i32) -> Option<AlbumResponse> {
        albums::table()
            .filter(id.eq(album_id))
            .inner_join(tracks::table)
            .select((Album::as_select(), Track::as_select()))
            .load::<(Album, Track)>(&mut self.conn())
            .ok()?
            .to_models()
            .into_iter()
            .next()
    }

    pub fn count(&self) -> Option<i64> {
        albums::table()
            .count()
            .get_result(&mut self.conn())
            .ok()
    }

    /**
     * Creates albums and returns the ID of the last album inserted.
     * Names is a vec of tuples where the first item is the album name and second is artist id (nullable)
     */
    pub async fn create_albums(&self, data: &Vec<(String, Option<i32>)>) -> Result<usize, diesel::result::Error> {
        let names: Vec<String> = data.iter().map(|f| f.0.clone()).collect();
        let artists: Vec<Option<i32>> = data.iter().map(|f| f.1).collect();
        
        insert_into(albums).values(
        names.iter().zip(artists.iter()).map(|(n, a)| (name.eq(n), artist_id.eq(a))).collect::<Vec<_>>()
        ).execute(&mut self.conn())
    }
}

impl DbConn for AlbumService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}