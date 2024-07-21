pub mod metadata;
mod formats;
mod util;
mod db;

use db::{album::init_albums_for_tracks, album_artist::init_album_artists_for_tracks};
use metadata::FileMetadata;
use time::{OffsetDateTime, PrimitiveDateTime};
use std::{fs::{DirEntry, File}, io::Read, path::PathBuf, time::{SystemTime, UNIX_EPOCH}};
use lofty::{file::{AudioFile, TaggedFileExt}, probe::read_from_path, tag::Tag};

use crate::{api::{albums::album_service::AlbumService, artists::artist_service::ArtistService}, files::get_file_directory};

use self::{formats::tag_extractor::TagExtractor, metadata::{CoverArt, TagMetadata}};


pub async fn scan_files(files: Vec<DirEntry>, artist_service: &ArtistService, album_service: &AlbumService) -> Vec<FileMetadata> {
    let mut scanned_files: Vec<FileMetadata> = Vec::with_capacity(files.len());

    for file in files {
        let tagged_file = match read_from_path(file.path()) {
            Ok(file) => file,
            Err(_) => {
                println!("Error reading file: {:?}", file.file_name());
                continue;
            }
        };

        let tags = tagged_file.tags();
        let properties = tagged_file.properties();
        let mut meta = FileMetadata::new();

        meta.path = file.path().to_str().unwrap().to_string();

        // Set size, duration, and bitrate
        meta.size = file.metadata().unwrap().len();
        meta.duration = properties.duration().as_secs();
        meta.bitrate = match properties.audio_bitrate() {
            Some(b) => Some(b as i32),
            None => None
        };

        // Set updated at (used for rescans)
        meta.updated_at = Some(match file.metadata() {
            Ok(file_meta) => match file_meta.modified() {
                Ok(file_date_modified) => {
                    system_time_to_primitive_datetime(file_date_modified)
                },
                Err(_err) => system_time_to_primitive_datetime(UNIX_EPOCH)
            },
            Err(_err) => system_time_to_primitive_datetime(UNIX_EPOCH)
        });

        let tag_meta = extract_metadata(tags, artist_service).await;

        // The title is the filename, unless a tag was provided
        meta.title = match tag_meta.title {
            Some(t) => Some(t),
            None => {
                Some(file.file_name().to_os_string().into_string()
                .unwrap_or("Default".to_owned()))
            }
        };

        meta.artist = tag_meta.artist;
        meta.album = tag_meta.album;
        meta.album_artist = tag_meta.album_artist;

        scanned_files.push(meta);
    }

    // Link album titles to albums in the DB
    init_album_artists_for_tracks(&mut scanned_files, artist_service).await;
    init_albums_for_tracks(&mut scanned_files, album_service).await;

    scanned_files
}

async fn extract_metadata(tags: &[Tag], artist_service: &ArtistService) -> TagMetadata {
    let tag_extractor = TagExtractor::new(artist_service);
    tag_extractor.extract(tags).await
}

pub fn get_cover_art(file_path: String) -> Option<CoverArt> {
    // Try to read the file, return none if failed
    let path = PathBuf::from(&file_path);
    let file = match read_from_path(path.clone()) {
        Ok(file) => file,
        Err(_) => return None
    };

    for tag in file.tags() {
        if tag.picture_count() > 0 {
            return Some(CoverArt {
                data: tag.pictures()[0].data().into(),
                mime_type: util::get_mime_type(
                    tag.pictures()[0].mime_type().unwrap_or(
                        &lofty::picture::MimeType::Unknown(String::new()
                    ))
                )
            })
        }
    };

    // If no embeded image, check the dir for a cover file
    let dir = get_file_directory(path);
    if let Err(_) = dir {
        return None;
    }

    let dir = dir.unwrap();

    // Check every file for a match of cover.extension
    for file in dir.into_iter() {
        // If the file is unaccessible, skip
        if let Err(_) = file {
            continue;
        }

        let file = file.unwrap();
        let file_name = file.file_name();
        let file_name = file_name.to_str().unwrap();
        if file_name.starts_with("cover") && (
            file_name.contains(".png") || file_name.contains(".jpg")
        )
            {
                match File::open(file.path()) {
                    Ok(mut cover) => {
                        let mut buf: Vec<u8> = [].to_vec();
                        let _ = cover.read_to_end(&mut buf);
                        return Some(CoverArt {
                            data: Box::from(buf),
                            mime_type: if file_name.ends_with(".png") {
                                "image/png".to_owned()
                            } else if file_name.ends_with(".jpg") {
                                "image/jpeg".to_owned()
                            } else {
                                "application/octet-stream".to_owned()
                            }
                        })
                    }
                    
                    Err(_) => continue, 
                }
            }
    }

    return None;
}

fn system_time_to_primitive_datetime(system_time: SystemTime) -> PrimitiveDateTime {
    let dt: OffsetDateTime = system_time.into();
    PrimitiveDateTime::new(dt.date(), dt.time())
}