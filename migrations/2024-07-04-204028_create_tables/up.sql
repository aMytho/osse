-- Your SQL goes here
PRAGMA foreign_keys = ON;

-- Drop migrations table if exists
DROP TABLE IF EXISTS "__diesel_schema_migrations";

-- Create migrations table
CREATE TABLE "__diesel_schema_migrations" (
    "version" TEXT PRIMARY KEY,
    "run_on" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- Drop albums table if exists
DROP TABLE IF EXISTS "albums";

-- Create albums table
CREATE TABLE "albums" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL,
    "artist_id" INTEGER,
    FOREIGN KEY ("artist_id") REFERENCES "artists" ("id") ON DELETE CASCADE
);

-- Drop artists table if exists
DROP TABLE IF EXISTS "artists";

-- Create artists table
CREATE TABLE "artists" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL
);

-- Drop tracks table if exists
DROP TABLE IF EXISTS "tracks";

-- Create tracks table
CREATE TABLE "tracks" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "title" TEXT NOT NULL,
    "artist_id" INTEGER,
    "duration" INTEGER NOT NULL,
    "size" BIGINT NOT NULL,
    "bitrate" INTEGER,
    "location" TEXT NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    "album_id" INTEGER,
    FOREIGN KEY ("artist_id") REFERENCES "artists" ("id") ON DELETE CASCADE,
    FOREIGN KEY ("album_id") REFERENCES "albums" ("id") ON DELETE CASCADE
);

-- Create playlists
CREATE TABLE "playlists" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" TEXT NOT NULL
);

-- Link tracks to playlists
CREATE TABLE "track_playlists" (
    "track_id" INTEGER NOT NULL,
    "playlist_id" INTEGER NOT NULL,
    PRIMARY KEY ("track_id", "playlist_id")
    FOREIGN KEY ("track_id") REFERENCES "tracks" ("id") ON DELETE CASCADE,
    FOREIGN KEY ("playlist_id") REFERENCES "playlists" ("id") ON DELETE CASCADE
);
