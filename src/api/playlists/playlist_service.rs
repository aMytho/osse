use diesel::{BelongingToDsl, r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, QueryDsl, RunQueryDsl, SelectableHelper, SqliteConnection};
use crate::{api::shared::service::DbConn, entities::{playlist::{Playlist, PlaylistTrack}, track::Track}, schema::{playlists, tracks}};
use crate::schema::playlists::dsl::*;
use crate::schema::playlist_tracks::dsl::*;


pub struct PlaylistService {
    pub db: Pool<ConnectionManager<SqliteConnection>>
}

impl PlaylistService {
    pub fn new(db: Pool<ConnectionManager<SqliteConnection>> ) -> PlaylistService {
        PlaylistService {db}
    }

    pub async fn get_playlist_by_id(&self, playlist: i32) -> Option<Playlist> {
        playlists
            .select(Playlist::as_select())
            .filter(playlists::id.eq(playlist))
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
    
    pub fn add_track_to_playlist(&self, track: i32, playlist: i32) -> Result<usize, diesel::result::Error>{
        diesel::insert_into(playlist_tracks)
            .values((track_id.eq(track), playlist_id.eq(playlist)))
            .execute(&mut self.conn())
    }

    pub fn playlist_tracks(&self, playlist: i32) -> Result<Vec<Track>, diesel::result::Error> {
        let playlist = playlists::table
            .select(Playlist::as_select())
            .filter(playlists::id.eq(playlist))
            .first(&mut self.conn())?;

        Ok(PlaylistTrack::belonging_to(&playlist)
                   .inner_join(tracks::table)
                   .select((PlaylistTrack::as_select(), Track::as_select()))
                   .load(&mut self.conn())?
                   .into_iter()
                   .map(|(_t, t2)| t2)
                   .collect())
    }

    pub fn remove_playlist(&self, playlist: i32) -> Result<usize, diesel::result::Error> {
        diesel::delete(playlists.filter(id.eq(playlist)))
            .execute(&mut self.conn())
    }

    pub fn remove_playlist_tracks(&self, playlist: i32, track: i32) -> Result<usize, diesel::result::Error> {
        diesel::delete(
            playlist_tracks
                .filter(playlist_id.eq(playlist))
                .filter(track_id.eq(track))
                )
            .execute(&mut self.conn())
    }
    
    pub fn count_playlist_tracks(&self, playlist: i32) -> Option<i64>{
        playlist_tracks
            .filter(playlist_id.eq(playlist))
            .count()
            .get_result(&mut self.conn())
            .ok()
    }

    pub fn edit_playlist_name(&self, playlist: i32, playlist_name: String) -> Result<usize, diesel::result::Error> {
        diesel::update(playlists.filter(id.eq(playlist)))
            .set(name.eq(playlist_name))
            .execute(&mut self.conn())
    }
}

impl DbConn for PlaylistService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}
