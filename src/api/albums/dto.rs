use std::collections::HashMap;

use serde::{Deserialize, Serialize};

use crate::entities::{album::Album, track::Track};


#[derive(Debug, Deserialize)]
pub struct AllAlbumsQuery {
    pub tracks: Option<bool>
}


#[derive(Clone, Serialize)]
pub struct AlbumResponse {
    pub id: i32,
    pub name: String,
    pub artist_id: Option<i32>,
    pub tracks: Option<Vec<Track>>
}

impl AlbumResponse {
    pub fn from(album: Album) -> AlbumResponse {
        AlbumResponse {
            artist_id: album.artist_id,
            name: album.name,
            id: album.id,
            tracks: None
        }
    }
    
    pub fn from_album_with_track(album: Album, tracks: Vec<Track>) -> AlbumResponse {
        AlbumResponse {
            artist_id: album.artist_id,
            name: album.name,
            id: album.id,
            tracks: Some(tracks)
        }
    }
}

pub trait Dto {
    fn to_models(self) -> Vec<AlbumResponse>;
}

impl Dto for Vec<Album> {
    fn to_models(self) -> Vec<AlbumResponse> {
        self.into_iter().map(|f| AlbumResponse::from(f)).collect()
    }
}

impl Dto for Vec<(Album, Track)> {
    /**
     * Converts to an album response and removes duplicate albums
     */
    fn to_models(self) -> Vec<AlbumResponse> {
        // hash map will store data
        let mut albums_map: HashMap<Album, Vec<Track>> = HashMap::with_capacity(self.len());

        // Each key is the album, since its a hash map duplicates are removed
        for (album, track) in self {
            albums_map.entry(album)
                .or_insert_with(|| Vec::new())
                .push(track);
        }

        albums_map
            .iter()
            .map(|(a, t)| AlbumResponse::from_album_with_track(a.clone(), t.to_owned()))
            .collect()
    }
}