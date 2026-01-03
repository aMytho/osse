import { EventEmitter, Injectable } from '@angular/core';
import { Artist } from './artist';

@Injectable({
  providedIn: 'root'
})
export class ArtistStoreService {
  public artists: Artist[] = [];
  public fetchingArists: number[] = [];
  public artistFetched = new EventEmitter<number>();

  constructor() { }

  public getArtistById(id: number) {
    return this.artists.find((a) => a.id == id);
  }

  public setArtist(artist: Artist) {
    if (this.artistIsLoaded(artist.id)) return;

    this.artists.push(artist);
  }

  public artistIsLoaded(id: number) {
    return this.artists.some((a) => a.id == id);
  }

  public addFetchingArtist(id: number) {
    this.fetchingArists.push(id);
  }

  public removeFetchingArtist(id: number) {
    this.fetchingArists = this.fetchingArists.filter((a) => a != id);
  }

  public isFetchingArtist(id: number) {
    return this.fetchingArists.includes(id);
  }
}
