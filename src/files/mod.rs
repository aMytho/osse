use std::{fs::{self, DirEntry, ReadDir}, path::PathBuf};

pub const ALLOWED_EXTENSIONS: [&str; 4] = [".mp3", ".wav", ".ogg", ".flac"];

pub enum FileError {
    DirectoryError
}

pub fn load_directory(dir: &String) -> Result<Vec<Vec<DirEntry>>, FileError> {
    let files = match fs::read_dir(&dir) {
        Ok(files) => files,
        Err(_err) => return Err(FileError::DirectoryError)
    };

    Ok(get_files_in_dir(files))
}

fn get_files_in_dir(files: ReadDir) -> Vec<Vec<DirEntry>> {
    let mut valid_files: Vec<Vec<DirEntry>> = Vec::new();
    let mut files_in_dir = Vec::new();
    for entry in files {
        let entry = match entry {
            Ok(ent) => ent,
            Err(_err) => continue
        };

        // If the entry is a directory, loop over it
        match entry.metadata() {
            Ok(m) => {
                if m.is_dir() {
                    if let Ok(d) = fs::read_dir(entry.path()) {
                        valid_files.append(&mut get_files_in_dir(d))
                    } 
                }
            },
            Err(_e) => continue
        };

        if let Some(file_name) = entry.file_name().to_str() {
            for file_extension in ALLOWED_EXTENSIONS {
                if file_name.ends_with(file_extension) {
                    files_in_dir.push(entry);
                    break;
                }
            }
        };
    };

    valid_files.push(files_in_dir);
    return valid_files;
}

pub fn get_file_directory(file: PathBuf) -> Result<ReadDir, std::io::Error>{
    // Get the path of the file
    PathBuf::from(file)
        .parent()
        .ok_or(std::io::Error::new(std::io::ErrorKind::NotFound, "Not Found"))?
        .read_dir()
}

