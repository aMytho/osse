import { Injectable } from '@angular/core';
import { PlayerService } from '../player.service';
import { TrackService } from '../../services/track/track.service';
import { ConfigService } from '../../services/config/config.service';
import { fetcher } from '../../util/fetcher';

@Injectable({
  providedIn: 'root'
})
export class PreloadService {
  private audioPlayer = new Audio();
  private isPreloadingTrack = false;

  constructor(private playerService: PlayerService, private trackService: TrackService, private configService: ConfigService) {
    // Setup the preload element.
    this.audioPlayer.muted = true;
    this.audioPlayer.autoplay = false;
    this.audioPlayer.preload = 'metadata';
    this.audioPlayer.crossOrigin = "anonymous";
    this.audioPlayer.addEventListener('loadedmetadata', () => {
      this.audioPlayer.pause();
    });

    this.playerService.trackPositionUpdate.subscribe((t) => {
      // Only check preload if we are not preloading something.
      if (this.isPreloadingTrack) {
        return;
      }

      // If we are 80% through the track, start next preload.
      if ((t.currentTimeSeconds / t.totalTimeSeconds) >= 0.8) {
        this.preloadNextTrack();
      }
    });

    // On track update, we mark the preload element as able to preload a new track.
    this.playerService.trackUpdated.subscribe((t) => {
      this.isPreloadingTrack = false;
    });
  }

  private async preloadNextTrack() {
    this.isPreloadingTrack = true;

    // Get the next track (if any)
    let track = this.trackService.getUpcomingTrack();
    if (track) {
      // Request authorization to preload.
      let req = await fetcher('tracks/' + track.id + '/stream?v=' + track.scannedAt);
      if (req.ok) {
        let res = await req.json();
        let token = res.token;
        let url = res.url;

        this.audioPlayer.src = url + '?token=' + token + '&id=' + this.configService.get('userID') + '&trackID=' + track.id;
        this.audioPlayer.load();
      }
    }
  }
}
