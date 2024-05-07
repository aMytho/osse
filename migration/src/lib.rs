pub use sea_orm_migration::prelude::*;

mod m20220101_000001_create_table;
mod m20240507_001012_create_tracks;
mod m20240509_021404_add_track_location;
mod m20240516_020819_create_albums;

pub struct Migrator;

#[async_trait::async_trait]
impl MigratorTrait for Migrator {
    fn migrations() -> Vec<Box<dyn MigrationTrait>> {
        vec![
            Box::new(m20220101_000001_create_table::Migration),
            Box::new(m20240507_001012_create_tracks::Migration),
            Box::new(m20240509_021404_add_track_location::Migration),
            Box::new(m20240516_020819_create_albums::Migration),
        ]
    }
}
