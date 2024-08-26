use lofty::tag::{Accessor, Tag};

use crate::{api::artists::artist_service::ArtistService, metadata::metadata::TagMetadata};

use super::target::{get_possible_tags, TagTarget};

/**
 * Contains logic to extract any type of supported tag
 */
pub struct TagExtractor<'a> {
    pub artist_service: &'a ArtistService,
    pub meta: TagMetadata,
    pub targets: Vec<TagTarget>
}

impl TagExtractor<'_> {
    pub fn new(artist_service: &ArtistService) -> TagExtractor {
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
                TagTarget::AlbumArtist => {
                    let result = self.get_custom_tag(tag, target);
                    match result {
                        Some(r) => {
                            self.targets.remove(index);
                            self.meta.album_artist = Some(r);
                            continue;
                        },
                        _ => ()
                    }

                }
                TagTarget::Artist => {
                    if let Some(artist) = tag.artist() {
                        if !artist.is_empty() {

                            // Insert the artist if its a new one, else return the id
                            let existing_artist = self.artist_service.get_artist_by_name(artist.to_string()).await;

                            if let None = existing_artist {
                                let new_artist = self.artist_service.create_artist(artist.to_string()).await;
                                if let Ok(new_artist) = new_artist {
                                    self.meta.artist = Some(new_artist);
                                }
                            } else {
                                self.meta.artist = Some(existing_artist.unwrap().id);
                            }

                            self.targets.remove(index);
                            continue;
                        }
                    }
                },
                TagTarget::Year => {
                    if let Some(year) = tag.year() {
                        self.meta.year = Some(year);
                        self.targets.remove(index);
                        continue;
                    }
                },
                TagTarget::Number => {
                    if let Some(number) = tag.track() {
                        self.meta.track_index = Some(number);
                        self.targets.remove(index);
                        continue;
                    }
                }
            }

            index += 1;
        }
    }

    /**
     * Stores tag data for a tag property that isn't covered by lofty (different in each implementation)
     */
    fn get_custom_tag(&self, tag: &Tag, target: &TagTarget) -> Option<String> {
        match target {
            TagTarget::AlbumArtist => {
                match tag.tag_type() {
                    lofty::tag::TagType::Ape => todo!(),
                    lofty::tag::TagType::Id3v2 => self.get_id3v2_data(tag, target),
                    lofty::tag::TagType::Mp4Ilst => todo!(),
                    lofty::tag::TagType::VorbisComments => self.get_vorbis_data(tag, target),
                    lofty::tag::TagType::RiffInfo => self.get_riff_info_data(tag, target),
                    lofty::tag::TagType::AiffText => todo!(),
                    // The generic tag covers all fields in ID3v1. Doesn't need custom implementation
                    _ => None,
                }
            },
            _ => None
        }
    }
}

