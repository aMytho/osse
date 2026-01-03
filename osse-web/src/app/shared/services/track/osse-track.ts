import { OsseArtist } from "../artist/osse-artist";

export interface OsseTrack {
  id: number;
  title: string;
  size: number;
  duration: number;

  bitrate: number | null;
  artist_ids: number[] | null;
  track_number: number | null;
  disc_number: number | null;
  cover_art_id: number | null;
  scanned_at: string;

  /**
   * Artist data. A track can have an artist ID without loading the artist, but most queries load it.
  */
  artists: OsseArtist[] | null;
}
