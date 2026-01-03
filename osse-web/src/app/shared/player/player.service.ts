import { EventEmitter, Injectable, signal, WritableSignal } from '@angular/core';
import { Track } from '../services/track/track';
import { TrackPlayerInfo, TrackUpdate } from './track-update';
import { PlaybackState } from './state-change';
import { ConfigService } from '../services/config/config.service';
import { BackgroundImageService } from '../ui/background-image.service';
import { BufferUpdate } from './buffer-update.interface';
import { TrackPosition } from './track-position.interface';
import { WebAudioService } from './web-audio.service';
import { ToastService } from '../../toast-container/toast.service';
import { fetcher } from '../util/fetcher';

@Injectable({
  providedIn: 'root'
})
export class PlayerService {
  /**
   * Runs whenever a track is changed.
   * This could be a new track, or just loading more buffer data
   */
  public trackUpdated = new EventEmitter<TrackUpdate>();
  public trackPositionUpdate = new EventEmitter<TrackPosition>();
  public stateChanged = new EventEmitter<PlaybackState>();
  public playbackEnded = new EventEmitter();
  public bufferUpdated = new EventEmitter<BufferUpdate>();
  private audioPlayer = new Audio();
  private track!: Track | null;
  private playbackRate: number = 1;

  private durationSignal: WritableSignal<number> = signal(0);
  private currenTimeSignal: WritableSignal<number> = signal(0);
  private isPlayingSignal: WritableSignal<boolean> = signal(false);

  constructor(
    private configService: ConfigService,
    private backgroundImageService: BackgroundImageService,
    private webAudioService: WebAudioService,
    private notificationService: ToastService
  ) {
    // Set up web audio
    // Cross origin is anonymous becuase it is a different origin, but we don't use credentials (cookies).
    this.audioPlayer.crossOrigin = "anonymous";
    this.webAudioService.setUp(this.audioPlayer);

    this.audioPlayer.addEventListener('timeupdate', (_ev) => {
      this.currenTimeSignal.set(this.audioPlayer.currentTime);
      this.trackPositionUpdate.emit({
        currentTimeSeconds: this.audioPlayer.currentTime,
        totalTimeSeconds: Math.max(this.track?.duration ?? 0, isNaN(this.audioPlayer.duration) ? 0 : this.audioPlayer.duration)
      });
    });

    this.audioPlayer.addEventListener('play', (_ev) => {
      this.isPlayingSignal.set(true)
      this.stateChanged.emit(PlaybackState.Playing);
      this.webAudioService.resumeIfSuspended();
    });
    this.audioPlayer.addEventListener('pause', (_ev) => {
      this.isPlayingSignal.set(false);
      this.stateChanged.emit(PlaybackState.Paused);
    });
    this.audioPlayer.addEventListener('ended', (_ev) => {
      this.isPlayingSignal.set(false);
      this.playbackEnded.emit();
    });
    this.audioPlayer.addEventListener('progress', (_ev) => {
      this.durationSignal.update((oldDuration: number) => {
        if (isNaN(this.audioPlayer.duration)) {
          return oldDuration;
        }

        return Math.max(oldDuration, this.audioPlayer.duration);
      });

      this.bufferUpdated.emit({ duration: this.duration(), buffered: this.audioPlayer.buffered })
    });
    this.audioPlayer.addEventListener('error', (_ev) => {
      this.notificationService.error('An error occurred while loading the audio file.');
    });

    this.audioPlayer.preload = "metadata";

    this.playbackRate = Number(localStorage.getItem('speed') ?? 1);
    this.audioPlayer.volume = Number(localStorage.getItem('volume') ?? 1);
  }

  public async setTrack(track: Track) {
    // Set next track
    this.track = track;

    // Set the real duration. Used for calculating buffer percentages later.
    // Not all formats list the end duration at the start of the track
    this.durationSignal.set(track.duration);

    // Get a token to access the file from the file server.
    let req = await fetcher('tracks/' + track.id + '/stream?v=' + track.scannedAt);
    if (req.ok) {
      this.trackUpdated.emit(new TrackUpdate(this.track, this.buildTrackInfo()));
      document.title = "Osse - " + this.track.title;

      let res = await req.json();
      let token = res.token;
      let url = res.url;

      this.audioPlayer.src = url + '?token=' + token + '&id=' + this.configService.get('userID') + '&trackID=' + track.id;
      // The playback rate is reset when a new track is loaded, set it again.
      this.audioPlayer.playbackRate = this.playbackRate;
    } else {
      this.notificationService.error('Failed to play track.');
    }
  }

  public async setTrackAndPlay(track: Track, duration: number = 0) {
    await this.setTrack(track);
    await this.play();

    // We do this last. It may slow down the player if it is first since it makes a network request.
    // Browsers are async, but our server isn't (yet).
    this.setBackgroundImage();
  }

  public setTrackAndBackgroundImage(track: Track) {
    this.setTrack(track);
    this.setBackgroundImage();
  }

  public setDuration(duration: number) {
    this.audioPlayer.currentTime = duration;
  }

  private setBackgroundImage() {
    this.backgroundImageService.setBG(this.track!.coverURL);
  }

  public play(time: number = this.audioPlayer.currentTime) {
    this.audioPlayer.currentTime = time;
    return new Promise<void>((resolve) => {
      this.audioPlayer.play()
        .then(resolve)
        .catch(resolve);
    });
  }

  public pause() {
    this.audioPlayer.pause();
  }

  public toggle() {
    if (this.isPlayingSignal()) {
      this.pause();
    } else {
      this.play();
    }
  }

  private buildTrackInfo(): TrackPlayerInfo {
    return {
      time: this.audioPlayer.currentTime,
      totalDurationEstimate: this.audioPlayer.duration
    }
  }

  public clearTrack() {
    this.audioPlayer.removeAttribute('src');
    this.audioPlayer.currentTime = 0;
    this.track = null;
    this.isPlayingSignal.set(false);
    this.playbackEnded.emit();
  }

  public setVolume(vol: number) {
    this.audioPlayer.volume = vol;
  }

  public getVolume(): number {
    return this.audioPlayer.volume;
  }

  public setSpeed(speed: number) {
    this.playbackRate = speed;
    this.audioPlayer.playbackRate = this.playbackRate;
  }

  public getSpeed() {
    return this.playbackRate;
  }

  /**
  * Skips forard or back in a song.
  * Handles going past the end or beggining of the song
  */
  public jumpDuration(duration: number, jumpForward = true) {
    if (jumpForward) {
      this.seek(this.audioPlayer.currentTime + duration);
    } else {
      this.seek(this.audioPlayer.currentTime - duration);
    }
  }

  private seek(duration: number) {
    // @ts-ignore This is valid because browser support for this function isn't good
    if (this.audioPlayer.fastSeek) {
      this.audioPlayer.fastSeek(duration);
    } else {
      this.audioPlayer.currentTime = duration;
    }
  }

  get duration() {
    return this.durationSignal.asReadonly();
  }

  get currentTime() {
    return this.currenTimeSignal.asReadonly();
  }

  get isPlaying() {
    return this.isPlayingSignal.asReadonly();
  }
}
