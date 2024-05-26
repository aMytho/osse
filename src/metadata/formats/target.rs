#[derive(Debug, PartialEq)]
pub enum TagTarget {
    Title,
    AlbumTitle,
    Artist,
    // Number,
    // Year,
    // Genre,
    // Comment,
}

pub fn get_possible_tags() -> Vec<TagTarget> {
    vec![TagTarget::Title, TagTarget::AlbumTitle, TagTarget::Artist]
}
