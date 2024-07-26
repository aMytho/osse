use diesel::{r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, QueryDsl, RunQueryDsl, SelectableHelper, SqliteConnection};
use crate::{api::shared::service::DbConn, entities::{playlist::Playlist, track::Track}, schema::{playlists, tracks, tracks_playlists}};
use crate::schema::playlists::dsl::*;
use crate::schema::tracks_playlists::dsl::*;


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
        diesel::insert_into(tracks_playlists)
            .values((track_id.eq(track), playlist_id.eq(playlist)))
            .execute(&mut self.conn())
    }

    pub fn playlist_tracks(&self, playlist: i32) -> Option<Vec<Track>> {
        // let playlist = playlists
        //     .select(Playlist::as_select())
        //     .filter(playlists::id.eq(playlist))
        //     .first(&mut self.conn())
        //     .ok()?;
            
        // playlists::table()
        //     .filter(id.eq(playlist))
        //     .inner_join(tracks_playlists::table)
        //     .inner_join(tracks::table)
        //     .select((Album::as_select(), tracks_playlists::all_columns, Track::as_select()))
        //     .load::<(Album, Track)>(&mut self.conn())
        //     .ok()?
        //     .to_models()
        //     .into_iter()
        //     .next()

        None
        
            
    }
}

impl DbConn for PlaylistService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}