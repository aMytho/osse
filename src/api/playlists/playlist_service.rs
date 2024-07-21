use diesel::{r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, QueryDsl, RunQueryDsl, SelectableHelper, SqliteConnection};
use crate::{api::shared::service::DbConn, entities::playlist::Playlist, schema::playlists};
use crate::schema::playlists::dsl::*;


pub struct PlaylistService {
    pub db: Pool<ConnectionManager<SqliteConnection>>
}

impl PlaylistService {
    pub fn new(db: Pool<ConnectionManager<SqliteConnection>> ) -> PlaylistService {
        PlaylistService {db}
    }

    pub async fn get_playlist_by_id(&self, playlist_id: i32) -> Option<Playlist> {
        playlists
            .select(Playlist::as_select())
            .filter(id.eq(playlist_id))
            .first(&mut self.conn())
            .ok()
    }

    pub async fn get_all(&self) -> Vec<Playlist> {
        playlists
            .select(Playlist::as_select())
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }
    
    pub async fn create_playlist(&self, playlist_name: String) -> Result<i32, diesel::result::Error> {
        diesel::insert_into(playlists)
            .values(name.eq(playlist_name))
            .returning(Playlist::as_returning())
            .get_result(&mut self.conn())
            .map(|a| a.id)
    }

    pub fn count(&self) -> Option<i64> {
        playlists::table
            .count()
            .get_result(&mut self.conn())
            .ok()
    }
}

impl DbConn for PlaylistService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}