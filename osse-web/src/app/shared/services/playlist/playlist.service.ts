import { Injectable } from '@angular/core';
import { Playlist } from './Playlist';
import { OssePlaylist } from './osse-playlist';
import { fetcher } from '../../util/fetcher';

@Injectable({
  providedIn: 'root'
})
export class PlaylistService {
  constructor() { }

  public async getPlaylist(id: number) {
    let req = await fetcher('playlists/' + id);
    let res = await req.json();

    if (req.ok) {
      return new Playlist(res);
    }

    throw "Playlist Error";
  }

  public async getAll(): Promise<Playlist[]> {
    let req = await fetcher('playlists');
    let res = await req.json();

    if (req.ok) {
      return res.map((p: OssePlaylist) => new Playlist(p));
    }

    throw "Playlist Error";
  }

  public async addTrackToPlaylist(playlistId: number, trackId: number) {
    await fetcher('playlists/' + playlistId + '/tracks/' + trackId, {
      method: 'POST'
    });
  }

  public addTracksToPlaylist(playlistId: number, trackIds: number[]) {
    return fetcher('playlists/' + playlistId + '/track-set', {
      method: 'POST',
      body: JSON.stringify({
        'track-ids': trackIds
      })
    });
  }

  public createPlaylist(name: string) {
    return fetcher('playlists', {
      method: 'POST',
      body: JSON.stringify({
        'name': name
      })
    });
  }
}
