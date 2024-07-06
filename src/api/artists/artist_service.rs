use diesel::{r2d2::{ConnectionManager, Pool, PooledConnection}, ExpressionMethods, SqliteConnection, QueryDsl, RunQueryDsl, SelectableHelper};
use crate::{api::shared::service::DbConn, entities::artist::Artist};
use crate::schema::artists::dsl::*;


pub struct ArtistService {
    pub db: Pool<ConnectionManager<SqliteConnection>>
}

impl ArtistService {
    pub fn new(db: Pool<ConnectionManager<SqliteConnection>> ) -> ArtistService {
        ArtistService {db}
    }

    pub async fn get_artist_by_id(&self, artist_id: i32) -> Option<Artist> {
        artists
            .select(Artist::as_select())
            .filter(id.eq(artist_id))
            .first(&mut self.conn())
            .ok()
    }
    
    pub async fn get_artist_by_name(&self, artist_name: String) -> Option<Artist> {
       artists
           .select(Artist::as_select())
           .filter(name.eq(artist_name))
           .first(&mut self.conn())
           .ok()
    }

    pub async fn get_artists_by_name(&self, names: Vec<String>) -> Vec<Artist> {
        artists
            .select(Artist::as_select())
            .filter(name.eq_any(names))
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }

    pub async fn get_all(&self) -> Vec<Artist> {
        artists
            .select(Artist::as_select())
            .load(&mut self.conn())
            .unwrap_or(Vec::new())
    }
    
    pub async fn create_artist(&self, artist_name: String) -> Result<i32, diesel::result::Error> {
        diesel::insert_into(artists)
            .values(name.eq(artist_name))
            .returning(Artist::as_returning())
            .get_result(&mut self.conn())
            .map(|a| a.id)
    }
}

impl DbConn for ArtistService {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>> {
        self.db.get().unwrap()
    }
}