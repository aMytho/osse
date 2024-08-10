use std::{fs::{self, DirEntry, ReadDir}, path::PathBuf};

pub const ALLOWED_EXTENSIONS: [&str; 3] = [".mp3", ".wav", ".ogg", ".ogg"];

pub enum FileError {
    DirectoryError
}

pub fn load_directory(dir: &String) -> Result<Vec<DirEntry>, FileError> {
    let files = match fs::read_dir(&dir) {
        Ok(files) => files,
        Err(_err) => return Err(FileError::DirectoryError)
    };

    let mut valid_files: Vec<DirEntry> = Vec::new();

    for entry in files {
        let entry = match entry {
            Ok(ent) => ent,
            Err(_err) => continue
        };

        let file_name = entry.file_name().to_str().unwrap_or("").to_string();

        for file_extension in ALLOWED_EXTENSIONS {
            if file_name.ends_with(file_extension) {
                valid_files.push(entry);
                break;
            }
        }
    };

    Ok(valid_files)
}

pub fn get_file_directory(file: PathBuf) -> Result<ReadDir, std::io::Error>{
    // Get the path of the file
    PathBuf::from(file)
        .parent()
        .ok_or(std::io::Error::new(std::io::ErrorKind::NotFound, "Not Found"))?
        .read_dir()
}

