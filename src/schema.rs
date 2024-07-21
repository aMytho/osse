// @generated automatically by Diesel CLI.

diesel::table! {
    albums (id) {
        id -> Integer,
        name -> Text,
        artist_id -> Nullable<Integer>,
    }
}

diesel::table! {
    artists (id) {
        id -> Integer,
        name -> Text,
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
    }
}

diesel::table! {
    tracks_playlists (id) {
        id -> Integer,
        track_id -> Integer,
        playlist_id -> Integer,
    }
}

diesel::joinable!(albums -> artists (artist_id));
diesel::joinable!(tracks -> albums (album_id));
diesel::joinable!(tracks -> artists (artist_id));
diesel::joinable!(tracks_playlists -> playlists (playlist_id));
diesel::joinable!(tracks_playlists -> tracks (track_id));

diesel::allow_tables_to_appear_in_same_query!(
    albums,
    artists,
    playlists,
    tracks,
    tracks_playlists,
);
