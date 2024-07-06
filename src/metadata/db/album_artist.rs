use crate::{api::artists::artist_service::ArtistService, metadata::metadata::FileMetadata};


pub async fn init_album_artists_for_tracks(files: &mut Vec<FileMetadata>, artist_service: &ArtistService) {
    // Get a list of album artists, remove duplicates
    let mut artist_list: Vec<String> = files.into_iter()
        .filter_map(|f| f.album_artist.to_owned())
        .collect::<Vec<String>>();
    artist_list.sort();
    artist_list.dedup();

    let artists = artist_service.get_artists_by_name(artist_list).await;

    // Add the album artist id to the file
    for file in files {
        if let Some(artist_name) = &file.album_artist {
            for artist in &artists {
                if &artist.name == artist_name {
                    file.album_artist_id = Some(artist.id);
                }
            }
        }
    }
}