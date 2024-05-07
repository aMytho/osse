use std::sync::Arc;

use sea_orm::{Database, DatabaseConnection};

use crate::{args, config::{self, AppConfig}};

lazy_static::lazy_static! {
    pub static ref CONFIG: Arc<AppConfig> = {
        let config_path = args::get_config_path();
        let config = match config::load_config(config_path) {
            Ok(config) => config,
            Err(err) => panic!("Failed to load config. Error: {:?}", err)
        };
        
        Arc::new(config)
    };
}

pub async fn get_db() -> DatabaseConnection {
    let db = Database::connect(CONFIG.database_address.clone()).await.unwrap();
    DatabaseConnection::from(db)
}