use std::future;

use poem::{web::Json, FromRequest, Result};
use serde::Deserialize;

#[derive(Deserialize)]
pub struct CreatePlaylist {
    pub playlist_id: i32,
    pub track_id: i32
}

impl<'a> FromRequest<'a> for CreatePlaylist {
    async fn from_request(
        req: &'a poem::Request,
        body: &mut poem::RequestBody,
    ) -> Result<Self> {
        
        // Get json data, check that it exists, no dupes
        println!("Test");
        todo!()
    }
    
}