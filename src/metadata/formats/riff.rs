use lofty::{ iff::wav::RiffInfoList, tag::{Accessor, Tag}};

use crate::{api::artists::artist_service, metadata::metadata::FileMetadata};

use super::target::TagTarget;

pub async fn get_supported_tag(tag: &Tag, mut meta: FileMetadata, targets: &mut Vec<TagTarget>) -> FileMetadata {
    let tag: RiffInfoList = RiffInfoList::from(tag.to_owned());
    
    let mut index: usize = 0;
    for _i in 0..targets.len() {
        let target = &targets[index];
        
        match target {
            TagTarget::Title => {
                if let Some(title) = tag.title() {
                    meta.title = Some(title.to_string());

                    targets.remove(index);
                    continue;
                }
            },
            TagTarget::AlbumTitle => {
                if let Some(album) = tag.album() {
                    // Check for other album fields
                    meta.album = Some(album.to_string());

                    targets.remove(index);
                    continue;
                }
            },
            TagTarget::Artist => {
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

                    targets.remove(index);
                    continue;
                }
            },
        }

        index += 1;
    }
    
    return meta;
}