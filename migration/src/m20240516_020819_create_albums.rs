use sea_orm_migration::prelude::*;

use crate::m20220101_000001_create_table::Artist;

#[derive(DeriveMigrationName)]
pub struct Migration;

#[async_trait::async_trait]
impl MigrationTrait for Migration {
    async fn up(&self, manager: &SchemaManager) -> Result<(), DbErr> {

        let _ = manager
            .create_table(
                Table::create()
                    .table(Album::Table)
                    .if_not_exists()
                    .col(
                        ColumnDef::new(Album::Id)
                            .integer()
                            .not_null()
                            .auto_increment()
                            .primary_key(),
                    )
                    .col(ColumnDef::new(Album::Name).string().not_null())
                    .col(ColumnDef::new(Album::ArtistId).integer().null())
                    .foreign_key(
                        ForeignKey::create()
                            .name("fk-album-artist_id")
                            .from(Album::Table, Album::ArtistId)
                            .to(Artist::Table, Artist::Id)
                    )
                    .to_owned(),
            )
            .await;
        manager.alter_table(
            Table::alter()
                .table(Track::Table)
                .add_column(
                    ColumnDef::new(Track::AlbumId)
                        .integer()
                        .null()
                )
                .add_foreign_key(
                    ForeignKey::create()
                        .name("fk-track-album_id")
                        .from(Track::Table, Track::AlbumId)
                        .to(Album::Table, Album::Id)
                        .get_foreign_key()
                )
                .to_owned()
        ).await
    }

    async fn down(&self, manager: &SchemaManager) -> Result<(), DbErr> {
        let _ = manager.
            drop_foreign_key(ForeignKey::drop()
                .name("fk-track-album_id").table(Track::Table).to_owned()
            )
            .await?;
        let _ = manager
            .drop_table(Table::drop().table(Album::Table).to_owned())
            .await?;
        manager
            .alter_table(
                Table::alter()
                    .table(Track::Table)
                    .drop_column(Alias::new("album_id"))
                    .to_owned()
            )
            .await
    }
}

#[derive(DeriveIden)]
enum Album {
    Table,
    Id,
    Name,
    ArtistId,
}

#[derive(DeriveIden)]
enum Track {
    Table,
    AlbumId
}