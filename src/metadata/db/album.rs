use crate::{api::album::album_service::AlbumService, metadata::metadata::FileMetadata};

/**
 * Creates a new album for each track if it doesn't already exist.
 * If it does, the existing album is used
 */
pub async fn init_albums_for_tracks(files: &mut Vec<FileMetadata>, album_service: &AlbumService<'_>) {
    // Get a list of albums, remove duplicates
    let mut album_list: Vec<String> = files.into_iter()
        .filter_map(|f| f.album.to_owned())
        .collect::<Vec<String>>();
    album_list.sort();
    album_list.dedup();

    // Find matching albums
    let albums = album_service.get_albums_by_name(album_list).await;

    // Try to find each result and link it to the existing albums
    let mut albums_to_add = Vec::with_capacity(files.len());
    for file in &mut *files {
        // If the file has an album, check for a match
        if let Some(file_album_name) = &file.album {
            for album in &albums {
                // If the album matches, set the id
                if file_album_name.eq(&album.name) {
                    file.album_id = Some(album.id);
                    file.album_artist_id = album.artist_id
                }
            }

            // If no match was found, queue album to be added
            if file.album_id == None {
                albums_to_add.push((file_album_name.to_string(), file.album_artist_id));
            }
        }
    }

    // Remove duplicates in existing album names
    albums_to_add.sort();
    albums_to_add.dedup();

    // Add each album that isn't already in the db
    let _ = album_service.create_albums(&albums_to_add).await;
    let new_albums = album_service.get_albums_by_name(albums_to_add.iter().map(|f| f.0.clone()).collect()).await;
    
    // Link new ids to files
    for file in &mut *files {
        // Skip files that already have a linked album
        if let Some(_) = file.album_id {
            continue;
        }

        // If the file has an album, check for a match
        if let Some(file_album_name) = &file.album {
            for album in &new_albums {
                // If the album matches, set the id
                if file_album_name.eq(&album.name) {
                    file.album_id = Some(album.id);
                }
            }
        }
    }

}