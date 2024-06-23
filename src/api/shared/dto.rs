use serde::Deserialize;

#[derive(Deserialize)]
pub struct GetById {
    pub id: i32
}