use sea_orm_migration::prelude::*;

#[derive(DeriveMigrationName)]
pub struct Migration;

#[async_trait::async_trait]
impl MigrationTrait for Migration {
    async fn up(&self, manager: &SchemaManager) -> Result<(), DbErr> {
        manager
            .alter_table(
                Table::alter()
                .table(Track::Table)
                .add_column(
                    ColumnDef::new(Track::Location)
                        .text()
                        .not_null()
                )
                .add_column(
                    ColumnDef::new(Track::UpdatedAt)
                        .timestamp()
                        .not_null()
                )
                .to_owned()
            )
            .await
    }

    async fn down(&self, manager: &SchemaManager) -> Result<(), DbErr> {
        manager
            .alter_table(Table::alter().table(Track::Table)
                .drop_column(Track::Location)
                .drop_column(Track::UpdatedAt)
                .to_owned())
            .await
    }
}

#[derive(DeriveIden)]
enum Track {
    Table,
    Location,
    UpdatedAt
}