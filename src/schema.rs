// @generated automatically by Diesel CLI.

diesel::table! {
    albums (id) {
        id -> Integer,
        name -> Text,
        artist_id -> Nullable<Integer>,
        year -> Nullable<Integer>,
    }
}

diesel::table! {
    artists (id) {
        id -> Integer,
        name -> Text,
    }
}

diesel::table! {
    playlist_tracks (track_id, playlist_id) {
        track_id -> Integer,
        playlist_id -> Integer,
    }
}

diesel::table! {
    playlists (id) {
        id -> Integer,
        name -> Text,
    }
}

diesel::table! {
    tracks (id) {
        id -> Integer,
        title -> Text,
        artist_id -> Nullable<Integer>,
        duration -> Integer,
        size -> BigInt,
        bitrate -> Nullable<Integer>,
        location -> Text,
        updated_at -> Timestamp,
        album_id -> Nullable<Integer>,
        year -> Nullable<Integer>,
        track_number -> Nullable<Integer>,
        disc_number -> Nullable<Integer>,
    }
}

diesel::joinable!(albums -> artists (artist_id));
diesel::joinable!(playlist_tracks -> playlists (playlist_id));
diesel::joinable!(playlist_tracks -> tracks (track_id));
diesel::joinable!(tracks -> albums (album_id));
diesel::joinable!(tracks -> artists (artist_id));

diesel::allow_tables_to_appear_in_same_query!(
    albums,
    artists,
    playlist_tracks,
    playlists,
    tracks,
);
