use lofty::{ogg::VorbisComments, tag::Tag};

use crate::metadata::formats::{tag_extractor::TagExtractor, target::TagTarget};

impl TagExtractor<'_> {
    pub fn get_vorbis_data(&self, tag: &Tag, target: &TagTarget) -> Option<String> {
        let tag = VorbisComments::from(tag.to_owned());
        
        match target {
            TagTarget::AlbumArtist => self.get_album_artist(&tag),
            TagTarget::AlbumYear => self.get_album_year(&tag),
            _ => None
        }
    }
}

trait VorbisExtractor {
    fn get_album_artist(&self, tag: &VorbisComments) -> Option<String>;
    fn get_album_year(&self, tag: &VorbisComments) -> Option<String>;
}

impl VorbisExtractor for TagExtractor<'_> {
    fn get_album_artist(&self, tag: &VorbisComments) -> Option<String> {
        tag.get("ALBUMARTIST").map(|f| f.to_string())
    }

    fn get_album_year(&self, tag: &VorbisComments) -> Option<String> {
        tag.get("DATE").map(|f| f.to_string())
    }
}

