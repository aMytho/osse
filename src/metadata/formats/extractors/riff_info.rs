use lofty::{iff::wav::RiffInfoList, tag::Tag};

use crate::metadata::formats::{tag_extractor::TagExtractor, target::TagTarget};

impl TagExtractor<'_> {
    pub fn get_riff_info_data(&self, tag: &Tag, target: &TagTarget) -> Option<String> {
        let tag = RiffInfoList::from(tag.to_owned());
        
        match target {
            TagTarget::AlbumArtist => self.get_album_artist(&tag),
            TagTarget::AlbumYear => self.get_album_year(&tag),
            _ => None
        }
    }
}

trait RiffInfoExtractor {
    fn get_album_artist(&self, tag: &RiffInfoList) -> Option<String>;
    fn get_album_year(&self, tag: &RiffInfoList) -> Option<String>;
}

impl RiffInfoExtractor for TagExtractor<'_> {
    fn get_album_artist(&self, tag: &RiffInfoList) -> Option<String> {
        tag.get("IART").map(|f| f.to_string())
    }

    fn get_album_year(&self, tag: &RiffInfoList) -> Option<String> {
       tag.get("IARL").map(|f| f.to_string())
    }
}
