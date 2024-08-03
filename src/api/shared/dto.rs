use serde::{Deserialize, Serialize};

#[derive(Deserialize, Serialize)]
pub struct GetById {
    pub id: i32
}

#[derive(Deserialize)]
pub struct GetByName {
    pub name: String
}