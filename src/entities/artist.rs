use serde::Serialize;
use diesel::prelude::*;

#[derive(Queryable, Selectable, Serialize)]
#[diesel(table_name = crate::schema::artists)]
#[diesel(check_for_backend(diesel::sqlite::Sqlite))]
pub struct Artist {
    pub id: i32,
    pub name: String,
}
