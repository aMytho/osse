use sea_orm_migration::prelude::*;
use super::m20220101_000001_create_table::Artist;

#[derive(DeriveMigrationName)]
pub struct Migration;

#[async_trait::async_trait]
impl MigrationTrait for Migration {
    async fn up(&self, manager: &SchemaManager) -> Result<(), DbErr> {
        manager
            .create_table(
                Table::create()
                    .table(Track::Table)
                    .if_not_exists()
                    .col(
                        ColumnDef::new(Track::Id)
                            .integer()
                            .not_null()
                            .auto_increment()
                            .primary_key(),
                    )
                    .col(ColumnDef::new(Track::Title).string().not_null())
                    .col(ColumnDef::new(Track::ArtistId).integer().null())
                    .foreign_key(
                        ForeignKey::create()
                            .name("fk-track-artist_id")
                            .from(Track::Table, Track::ArtistId)
                            .to(Artist::Table, Artist::Id)
                    )
                    .col(ColumnDef::new(Track::Duration).integer().not_null())
                    .col(ColumnDef::new(Track::Size).big_integer().not_null())
                    .col(ColumnDef::new(Track::Bitrate).integer().null())
                    .to_owned(),
            )
            .await
    }

    async fn down(&self, manager: &SchemaManager) -> Result<(), DbErr> {
        manager
            .drop_table(Table::drop().table(Track::Table).to_owned())
            .await
    }
}

#[derive(DeriveIden)]
enum Track {
    Table,
    Id,
    Title,
    ArtistId,
    Duration,
    Size,
    Bitrate
}
