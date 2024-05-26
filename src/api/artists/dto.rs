use serde::Deserialize;

#[derive(Deserialize)]
pub struct GetArtistByid {
    pub id: i32
}