import { Injectable } from '@angular/core';
import { Artist } from './artist/artist';
import { fetcher } from '../util/fetcher';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  constructor() { }

  // TODO: Move this to an artist service.
  public async getArtist(id: number): Promise<Artist | null> {
    let request = await fetcher(`artists/${id}`);
    if (request.ok) {
      let artist = await request.json();
      return new Artist(artist);
    } else {
      return null;
    }
  }
}
