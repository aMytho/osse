import { WritableSignal, computed, signal } from "@angular/core";
import { LocatorService } from "../../../locator.service";
import { getNicelyFormattedTime } from "../../util/time";
import { ApiService } from "../api.service";
import { ArtistStoreService } from "../artist/artist-store.service";
import { OsseTrack } from "./osse-track";
import { Artist } from "../artist/artist";
import { v4 as uuid } from 'uuid';
import { ConfigService } from "../config/config.service";

export class Track {
  public track!: OsseTrack;
  private artistStore: ArtistStoreService = LocatorService.injector.get(ArtistStoreService);
  private apiService: ApiService = LocatorService.injector.get(ApiService);
  private configService: ConfigService = LocatorService.injector.get(ConfigService);
  public bufferSize: number = 0;
  public artists: WritableSignal<Artist[]> = signal([]);
  public artistPrimary = computed(() => {
    let artists = this.artists();
    return artists.at(0);
  });
  public artistNames = computed(() => {
    let names = this.artists().map((a) => a.name);
    if (names.length == 0) {
      return '(None)';
    }

    if (names.length == 1) {
      return names[0];
    }

    if (names.length == 2) {
      return names.join(' and ')
    }

    let allButLastName = names.slice(0, -1).join(', ');
    let lastName = names[names.length - 1];
    return `${allButLastName}, and ${lastName}`;
  });
  /**
  * Generates a random uuid. Use for a unique identifier instead of track ID. This changes each time this class is created.
  * Track IDs should be used for server communication only.
  */
  public uuid: string;

  constructor(track: OsseTrack) {
    this.track = track;
    this.uuid = uuid();

    // Grab the artist info
    this.getArtist();
  }

  public get id() {
    return this.track.id;
  }

  public get title() {
    return this.track.title;
  }

  public get size() {
    return this.track.size;
  }

  public get duration() {
    return this.track.duration;
  }

  public get durationFormatted() {
    return getNicelyFormattedTime(this.track.duration);
  }

  public hasArtist(): boolean {
    return this.track.artists != null && this.track.artists.length > 0;
  }

  public async getArtist() {
    if (!this.hasArtist()) {
      return;
    }

    // If we have the data in the request, use it.
    if (this.track.artists != null) {
      this.artists.set(this.track.artists.map((a) => new Artist(a)));
      return;
    }

    // Check if it already exists in the store.
    for (let artistId of this.track.artist_ids ?? []) {
      if (this.artistStore.artistIsLoaded(artistId)) {
        this.addArtistById(artistId);
        return;
      }
    }

    // We need to fetch artist. Check the fetch list to make sure we don't make multiple reqeuests for the same artist.
    for (let artistId of this.track.artist_ids ?? []) {
      if (this.artistStore.isFetchingArtist(artistId)) {
        await new Promise<void>((resolve, _reject) => {
          let sub = this.artistStore.artistFetched.subscribe((_v) => {
            sub.unsubscribe();
            resolve();
          });
        });

        this.addArtistById(artistId);
        return;
      }
    }

    // Not fetching artist, start fetching artist
    for (let artistId of this.track.artist_ids ?? []) {
      this.artistStore.addFetchingArtist(artistId);
      let artist = await this.apiService.getArtist(artistId);
      if (artist) {
        this.artistStore.setArtist(artist);
        this.addArtistById(artistId);
      }

      this.artistStore.removeFetchingArtist(artistId);
      this.artistStore.artistFetched.emit(artistId);
    }
  }

  private addArtistById(artistId: number) {
    let artist = this.artistStore.getArtistById(artistId) ?? null;
    this.artists.update((a) => {
      a.push(artist as Artist);
      return a;
    });
  }

  /**
  * Sometimes tracks are fetched from osse and made into classes, but the user can refer to the same instance.
  * Later, these instances are in the same array.
  * In cases like this, we need a fresh uuid and track instance.
  * An example is the tracklist page. Each track is a Track class, so adding the same track to the queue will result in duplicate uuids!
  */
  public regenerateTrack(): Track {
    return new Track({
      id: this.track.id,
      title: this.track.title,
      size: this.track.size,
      duration: this.track.duration,
      track_number: this.trackNumber,
      disc_number: this.discNumber,
      bitrate: this.track.bitrate,
      cover_art_id: this.track.cover_art_id,
      scanned_at: this.track.scanned_at,
      artist_ids: this.track.artists?.map((a) => a.id) ?? null,
      artists: this.track.artists
    });
  }

  public get trackNumber() {
    return this.track.track_number;
  }

  public get discNumber() {
    return this.track.disc_number;
  }

  public get scannedAt() {
    return this.track.scanned_at;
  }

  get coverURL() {
    return this.configService.get('apiURL') + "api/cover-art/" + this.track.cover_art_id;
  }
}
