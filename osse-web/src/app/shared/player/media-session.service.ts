import { Injectable } from '@angular/core';
import { PlayerService } from './player.service';
import { TrackService } from '../services/track/track.service';
import { PlaybackState } from './state-change';

@Injectable({
  providedIn: 'root'
})
export class MediaSessionService {
  constructor(
    private playerService: PlayerService,
    private trackService: TrackService
  ) {
    if ("mediaSession" in window.navigator) {
      try {
        this.listenForMediaEvents();
      } catch (error) { }
      try {
        this.listenForPlayerEvents();
      } catch (error) { }
    }
  }

  private listenForMediaEvents() {
    navigator.mediaSession.setActionHandler("play", () => {
      this.playerService.play();
    });
    navigator.mediaSession.setActionHandler("pause", () => {
      this.playerService.pause();
    });
    navigator.mediaSession.setActionHandler("stop", () => {
      this.playerService.pause();
    });
    navigator.mediaSession.setActionHandler("previoustrack", () => {
      this.trackService.moveToLastTrack();
    });
    navigator.mediaSession.setActionHandler("nexttrack", () => {
      this.trackService.moveToNextTrack();
    });
    navigator.mediaSession.setActionHandler('seekforward', (ev) => {
      this.playerService.jumpDuration(ev.seekOffset || 10, true);
    });
    navigator.mediaSession.setActionHandler('seekbackward', (ev) => {
      this.playerService.jumpDuration(ev.seekOffset || 10, false);
    });
  }

  private listenForPlayerEvents() {
    this.playerService.trackUpdated.subscribe((t) => {
      navigator.mediaSession.metadata = new MediaMetadata({
        title: t.title,
        artist: t.artist?.name ?? 'Unknown Artist',
        artwork: [
          {
            src: t.cover
          }
        ]
      })
    });

    this.playerService.stateChanged.subscribe((s) => {
      if (s == PlaybackState.Playing) {
        navigator.mediaSession.playbackState = "playing";
      } else {
        navigator.mediaSession.playbackState = "paused";
      }
    });

    this.playerService.playbackEnded.subscribe((_) => navigator.mediaSession.playbackState = "none");
  }
}
