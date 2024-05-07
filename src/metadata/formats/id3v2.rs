use lofty::{id3::v2::Id3v2Tag, tag::{Accessor, Tag}};

use crate::{api::artists::artist_service, metadata::metadata::FileMetadata};

pub async fn get_supported_tag(tag: &Tag, mut meta: FileMetadata) -> FileMetadata {
    let tag: Id3v2Tag = Id3v2Tag::from(tag.to_owned());
    
    if let Some(title) = tag.title() {
        meta.title = Some(title.to_string());
    }

    if let Some(album) = tag.album() {
        // Check for other album fields
        meta.album = Some(album.to_string());
    }

    if let Some(artist) = tag.artist() {
        // Insert the artit if its a new one, else return the id
        let existing_artist = artist_service::get_artist_by_name(artist.to_string()).await;
        if let None = existing_artist {
            let new_artist = artist_service::create_artist(artist.to_string()).await;
            if let Ok(new_artist) = new_artist {
                meta.artist = Some(new_artist.id);
            }
        } else {
            meta.artist = Some(existing_artist.unwrap().id);
        }

    }

    return meta;
}