use serde::Deserialize;

#[derive(Deserialize)]
pub struct GetById {
    pub id: i32
}

#[derive(Deserialize)]
pub struct GetByName {
    pub name: String
}