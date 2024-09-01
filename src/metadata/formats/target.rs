#[derive(Debug, PartialEq)]
pub enum TagTarget {
    Title,
    AlbumTitle,
    AlbumArtist,
    Artist,
    Number,
    Year,
    AlbumYear
    // Genre,
    // Comment,
}

/**
 * Returns a vec of all the tags we look for.
 */
pub fn get_possible_tags() -> Vec<TagTarget> {
    vec![TagTarget::Title, TagTarget::AlbumTitle, TagTarget::AlbumArtist, TagTarget::Artist, TagTarget::Year, TagTarget::Number, TagTarget::AlbumYear]
}
