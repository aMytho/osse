use lofty::{id3::v2::{FrameId, Id3v2Tag}, tag::Tag};

use crate::metadata::formats::{tag_extractor::TagExtractor, target::TagTarget};

impl TagExtractor<'_> {
    pub fn get_id3v2_data(&self, tag: &Tag, target: &TagTarget) -> Option<String> {
        let tag = Id3v2Tag::from(tag.to_owned());
        
        match target {
            TagTarget::AlbumArtist => self.get_album_artist(&tag),
            TagTarget::AlbumYear => self.get_album_year(&tag),
            _ => None
        }
    }
}

trait Id2v2Extractor {
    fn get_album_artist(&self, tag: &Id3v2Tag) -> Option<String>;
    fn get_album_year(&self, tag: &Id3v2Tag) -> Option<String>;
}

impl Id2v2Extractor for TagExtractor<'_> {
    fn get_album_artist(&self, tag: &Id3v2Tag) -> Option<String> {
        let id = FrameId::new("TPE2").unwrap();
        match tag.get_text(&id) {
            Some(v) => Some(v.to_string()),
            None => None
        }
    }

    fn get_album_year(&self, tag: &Id3v2Tag) -> Option<String> {
        let id = FrameId::new("TYER").unwrap();
        match tag.get_text(&id) {
            Some(v) => Some(v.to_string()),
            None => None
        }
    }
}
