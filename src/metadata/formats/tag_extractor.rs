use lofty::tag::{Accessor, Tag};

use crate::{api::artists::artist_service::ArtistService, metadata::metadata::TagMetadata};

use super::target::{get_possible_tags, TagTarget};

/**
 * Contains logic to extract any type of supported tag
 */
pub struct TagExtractor<'a> {
    pub artist_service: &'a ArtistService<'a>,
    pub meta: TagMetadata,
    pub targets: Vec<TagTarget>
}

impl TagExtractor<'_> {
    pub fn new<'a>(artist_service: &'a ArtistService<'a>) -> TagExtractor<'a> {
        TagExtractor {artist_service, meta: TagMetadata::new(), targets: get_possible_tags()}
    }

    pub async fn extract(mut self, tags: &[Tag]) -> TagMetadata {
        for tag in tags {
            self.store_tag_data(tag).await;
        }

        self.meta
    }

    async fn store_tag_data(&mut self, tag: &Tag) {
        let mut index: usize = 0;
        for _i in 0..self.targets.len() {
            let target = &self.targets[index];
            
            match target {
                TagTarget::Title => {
                    if let Some(title) = tag.title() {
                        self.meta.title = Some(title.to_string());
    
                        self.targets.remove(index);
                        continue;
                    }
                },
                TagTarget::AlbumTitle => {
                    if let Some(album) = tag.album() {
                        // Check for other album fields
                        self.meta.album = Some(album.to_string());
    
                        self.targets.remove(index);
                        continue;
                    }
                },
                TagTarget::Artist => {
                    if let Some(artist) = tag.artist() {
                        // Insert the artist if its a new one, else return the id
                        let existing_artist = self.artist_service.get_artist_by_name(artist.to_string()).await;
                       
                        if let None = existing_artist {
                            let new_artist = self.artist_service.create_artist(artist.to_string()).await;
                            if let Ok(new_artist) = new_artist {
                                self.meta.artist = Some(new_artist.id);
                            }
                        } else {
                            self.meta.artist = Some(existing_artist.unwrap().id);
                        }
    
                        self.targets.remove(index);
                        continue;
                    }
                },
            }

            index += 1;
        }
    }
}
