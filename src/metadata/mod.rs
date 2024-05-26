mod metadata;
mod formats;
mod util;

use metadata::FileMetadata;
use std::{fs::{DirEntry, File}, io::Read, path::PathBuf};
use sea_orm::entity::prelude::DateTime;
use chrono::{DateTime as ChronoTime, Utc};
use lofty::{file::{AudioFile, TaggedFileExt}, probe::read_from_path, properties::FileProperties, tag::Tag};

use crate::files::get_file_directory;

use self::{formats::{target::get_possible_tags, id3v1, id3v2}, metadata::CoverArt};


pub async fn scan_files(files: Vec<DirEntry>) -> Vec<FileMetadata> {
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

        scanned_files.push(extract_metadata(&file, tags, properties).await);
    }

    scanned_files
}

async fn extract_metadata(file: &DirEntry, tags: &[Tag], properties: &FileProperties) -> FileMetadata {
    let mut meta = FileMetadata::new();

    // The title is the filename by default, a later title tag will override this
    meta.title = Some(file.file_name().to_os_string().into_string()
        .unwrap_or("Default".to_owned()));

    // Get the last date the file was modified (used to see if we need to re-scan)
    meta.updated_at = Some(match file.metadata() {
        Ok(file_meta) => match file_meta.modified() {
            Ok(file_date_modified) => {
                let date_time: ChronoTime<Utc> = file_date_modified.into();
                DateTime::parse_from_str(date_time.to_rfc3339().as_str(), "%Y-%m-%d %H:%M:%S")
                    .unwrap_or(DateTime::default())
            }
            Err(_err) => DateTime::default()
        },
        Err(_err) => DateTime::default()
    });

    // Get all possible tag entries we use
    let mut target_tags = get_possible_tags();

    for tag in tags {
        match tag.tag_type() {
            lofty::tag::TagType::Ape => todo!(),
            lofty::tag::TagType::Id3v1 => meta = id3v1::get_supported_tag(&tag, meta, &mut target_tags).await,
            lofty::tag::TagType::Id3v2 => meta = id3v2::get_supported_tag(&tag, meta, &mut target_tags).await,
            lofty::tag::TagType::Mp4Ilst => todo!(),
            lofty::tag::TagType::VorbisComments => todo!(),
            lofty::tag::TagType::RiffInfo => todo!(),
            lofty::tag::TagType::AiffText => todo!(),
            _ => todo!(),
        }
    }

    meta.path = file.path().to_str().unwrap().to_string();

    // Get size and duration info
    meta.duration = properties.duration().as_secs();
    meta.bitrate = match properties.audio_bitrate() {
        Some(b) => Some(b as i32),
        None => None
    };
    meta.size = file.metadata().unwrap().len();

    meta
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
                        &lofty::picture::MimeType::Unknown("".to_owned()
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
                                "image/unknown".to_owned()
                            }
                        })
                    }
                    
                    Err(_) => continue, 
                }
            }
    }

    return None;
}
