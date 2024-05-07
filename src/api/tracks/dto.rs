use serde::Deserialize;

#[derive(Deserialize)]
pub struct GetCoverArtById {
    pub id: i32
}