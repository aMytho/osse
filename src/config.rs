use std::{fs::File, io::Read};

use serde::{Serialize, Deserialize};

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct AppConfig {
    pub server_address: String,
    pub server_port: u16,
    pub database_address: String,
    pub files: Vec<String>
}

#[derive(Debug)]
pub enum ConfigError {
    FileNotFound,
    ContentsNotValid
}

pub fn load_config(path: String) -> Result<AppConfig, ConfigError> {
    let file = File::open(path);
    let mut file = match file {
        Ok(val) => val,
        Err(_) => return Err(ConfigError::FileNotFound),
    };

    let mut contents = String::new();
    file.read_to_string(&mut contents).expect("Unable to read config");

    match serde_json::from_str(&contents) {
        Ok(val) => Ok(val),
        Err(_err) => Err(ConfigError::ContentsNotValid)
    }
}