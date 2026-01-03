import { LocatorService } from "../../../locator.service";
import { ApiService } from "../api.service";
import { Artist } from "../artist/artist";
import { Track } from "../track/track";
import { OsseAlbum } from "./osse-album";

export class Album {
  private trackList: Track[] = [];
  private artistInfo: Artist[] = [];
  private apiService: ApiService = LocatorService.injector.get(ApiService);

  constructor(public album: OsseAlbum) {
    album.tracks?.forEach(track => {
      this.trackList.push(new Track(track));
    });

    this.getArtistIfExists();
  }

  public get id() {
    return this.album.id;
  }

  public get name() {
    return this.album.name;
  }

  public get tracks() {
    return this.trackList;
  }

  public get artist() {
    return this.artistInfo;
  }

  public get year() {
    return this.album.year;
  }

  private getArtistIfExists() {
    // If we loaded the artists, init the Artist classes.
    if (this.album.artists != null) {
      this.artistInfo = this.album.artists.map((a) => new Artist(a));
      return;
    }

    // If artists exist but were not loaded, load them async
    for (let artistId of this.album.artist_ids ?? []) {
      this.apiService.getArtist(artistId).then(val => this.artistInfo.push(val as Artist));
    }
  }
}
