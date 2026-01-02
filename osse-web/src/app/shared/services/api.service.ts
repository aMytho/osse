import { Injectable } from '@angular/core';
import { ConfigService } from './config/config.service';
import { Track } from './track/track';
import { OsseTrack } from './track/osse-track';
import { Artist } from './artist/artist';
import { Album } from './album/Album';
import { fetcher } from '../util/fetcher';

@Injectable({
  providedIn: 'root'
})
export class ApiService {

  constructor(private configService: ConfigService) { }

  public async getAllTracks(): Promise<Track[]> {
    try {
      let request = await fetch(`${this.configService.get('apiURL')}tracks/all`);
      let response = await request.json();

      return response.map((track: OsseTrack) => new Track(track))
    } catch (e) {
      return [];
    }
  }

  public async getArtist(id: number): Promise<Artist | null> {
    let request = await fetcher(`artists/${id}`);
    if (request.ok) {
      let artist = await request.json();
      return new Artist(artist);
    } else {
      return null;
    }
  }

  public async getAlbumWithTracks(id: number): Promise<Album | null> {
    let request = await fetch(`${this.configService.get('apiURL')}albums/${id}/tracks`);
    if (request.ok) {
      let album = await request.json();
      return new Album(album);
    } else {
      return null;
    }
  }

  public get url() {
    return this.configService.get('apiURL') + '/api/';
  }
}
