import { fetcher } from "../../util/fetcher";
import { OsseTrack } from "../track/osse-track";
import { Track } from "../track/track";
import { OssePlaylist } from "./osse-playlist";

export class Playlist {
  public tracks: Track[] = [];

  constructor(private playlist: OssePlaylist) {
    if (playlist.tracks == undefined) {
      return
    }
    this.tracks = playlist.tracks.map(t => {
      return new Track(t);
    })
  }

  public get id() {
    return this.playlist.id;
  }

  public get name() {
    return this.playlist.name;
  }

  public get count() {
    return this.playlist.tracks_count;
  }

  public async requestTracks() {
    let req = await fetcher('playlists/' + this.id + '/tracks');
    if (req.ok) {
      let res = await req.json();
      this.tracks = res.map((t: OsseTrack) => new Track(t));
    }

    return this.tracks;
  }
}
