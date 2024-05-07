use lofty::picture::MimeType;

pub fn get_mime_type(mime: &MimeType) -> String {
    match mime {
        MimeType::Png => "iamge/png".to_owned(),
        MimeType::Jpeg => "image/jpeg".to_owned(),
        MimeType::Tiff => "image/tiff".to_owned(),
        MimeType::Bmp => "image/bmp".to_owned(),
        MimeType::Gif => "image/gif".to_owned(),
        MimeType::Unknown(mime) => mime.to_owned(),
        _ => "image/unknown".to_owned(),
    }
}