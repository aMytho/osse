-- This file should undo anything in `up.sql`
PRAGMA foreign_keys = ON;
DROP TABLE tracks;
DROP TABLE artists;
DROP TABLE albums;
DROP TABLE playlists;
DROP TABLE playlist_tracks;
