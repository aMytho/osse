use diesel::prelude::*;
use serde::Serialize;

#[derive(Queryable, Selectable, Serialize, Identifiable)]
#[diesel(table_name = crate::schema::artists)]
#[diesel(check_for_backend(diesel::sqlite::Sqlite))]
pub struct Artist {
    pub id: i32,
    pub name: String,
}
