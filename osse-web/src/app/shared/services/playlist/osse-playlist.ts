import { OsseTrack } from "../track/osse-track";

export interface OssePlaylist {
  id: number;
  name: string;
  tracks: OsseTrack[];
  // This is used when we don't load the track relation.
  tracks_count: number;
}
