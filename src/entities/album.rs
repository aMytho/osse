use diesel::prelude::*;
use serde::Serialize;
use crate::entities::artist::Artist;


#[derive(Serialize)]
#[derive(Queryable, Selectable, Identifiable, Associations, Debug, Eq, PartialEq, Hash, Clone)]
#[diesel(belongs_to(Artist))]
#[diesel(table_name = crate::schema::albums)]
pub struct Album {
    pub id: i32,
    pub name: String,
    pub artist_id: Option<i32>,
}

impl Album {
    pub fn to_model(&self) -> (i32, String, Option<i32>) {
        (self.id, self.name.clone(), self.artist_id)
    }
}
