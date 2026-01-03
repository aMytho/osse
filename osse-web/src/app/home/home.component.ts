import { Component, computed, OnDestroy, OnInit, signal, WritableSignal } from '@angular/core';
import { TrackComponent } from './track/track.component';
import { TrackService } from '../shared/services/track/track.service';
import { Track } from '../shared/services/track/track';
import { PlayerService } from '../shared/player/player.service';
import { PlaybackState } from '../shared/player/state-change';
import { ConfigService } from '../shared/services/config/config.service';
import { IconComponent } from '../shared/ui/icon/icon.component';
import { mdiFastForward, mdiInformation, mdiRepeat, mdiRewind, mdiShuffle, mdiSilverwareForkKnife, mdiDeleteSweep, mdiRepeatOff, mdiRepeatOnce, mdiRestore, mdiCog } from '@mdi/js';
import { ModalService } from '../shared/ui/modal/modal.service';

import { Subscription } from 'rxjs';
import { HeaderComponent } from '../shared/ui/header/header.component';
import { AlbumArtFullscreenComponent } from '../shared/ui/modals/album-art-fullscreen/album-art-fullscreen.component';
import { ToastService } from '../toast-container/toast.service';
import { TrackInfoComponent } from '../shared/ui/modals/track-info/track-info.component';
import { Repeat } from '../shared/services/track/repeat.enum';
import { VisualizerComponent } from '../shared/player/visualizer/visualizer.component';
import { PlayerSettingsComponent } from '../shared/ui/modals/player-settings/player-settings.component';
import { CommonModule } from '@angular/common';
import { AddToPlaylistFactoryComponent } from '../shared/ui/modals/add-to-playlist-factory/add-to-playlist-factory.component';

@Component({
  selector: 'app-home',
  imports: [CommonModule, IconComponent, TrackComponent, HeaderComponent, VisualizerComponent],
  templateUrl: './home.component.html'
})
export class HomeComponent implements OnInit, OnDestroy {
  public bg: WritableSignal<string> = signal("assets/img/osse.webp");
  public tracks: WritableSignal<Track[]> = signal([]);
  public playing: boolean = false;
  public title: WritableSignal<string> = signal('No Track Found');
  public artist: WritableSignal<string> = signal('');
  public repeat = computed(() => {
    switch (this.trackService.repeat()) {
      case Repeat.None: return mdiRepeatOff;
      case Repeat.Once: return mdiRepeatOnce;
      case Repeat.Loop: return mdiRepeat;
    }
  });
  public repeatActive = computed(() => {
    let repeat = this.trackService.repeat();
    return repeat == Repeat.Once || repeat == Repeat.Loop;
  });
  public repeatTooltip = computed(() => {
    switch (this.trackService.repeat()) {
      case Repeat.None: return 'Repeat Off';
      case Repeat.Once: return 'Repeat Once';
      case Repeat.Loop: return 'Repeat Until Stopped';
    }
  });
  public tracksCanBeRestored: WritableSignal<boolean> = signal(false);
  public showVisualizer: WritableSignal<boolean> = signal(true);

  private trackUpdated!: Subscription;
  private playbackEnded!: Subscription;
  private stateChanged!: Subscription;

  forward = mdiFastForward;
  back = mdiRewind;
  shuffle = mdiShuffle;
  info = mdiInformation;
  consume = mdiSilverwareForkKnife;
  clear = mdiDeleteSweep;
  restore = mdiRestore;
  settings = mdiCog;

  constructor(
    public trackService: TrackService,
    private playerService: PlayerService,
    private configService: ConfigService,
    private modalService: ModalService,
    private notificationService: ToastService
  ) { }

  public onPlayerToggle() {
    // If no track, don't respond to button click
    if (!this.trackService.activeTrack) return;
    this.playing = !this.playing;

    if (!this.playing) {
      this.playerService.pause();
      return;
    } else {
      this.playerService.play();
    }
  }

  public onNextTrack() {
    this.trackService.moveToNextTrack();
  }

  public onPreviousTrack() {
    this.trackService.moveToLastTrack();
  }

  public playTrack(index: number) {
    this.trackService.moveToTrack(index);
  }

  public removeTrack(index: number) {
    this.trackService.removeTrack(index);
  }

  public addToPlaylist(track: Track) {
    this.modalService.setDynamicModal(AddToPlaylistFactoryComponent, [{
      name: 'tracks',
      val: [track]
    }], 'Add to Playlist');
    this.modalService.show();
  }

  public shuffleTracks() {
    this.trackService.shuffle();
    this.tracks.set(this.trackService.tracks);
  }

  public toggleConsume() {
    this.trackService.consume.update((v) => !v);
  }

  public toggleRepeat() {
    this.trackService.repeat.update((v) => {
      switch (v) {
        case Repeat.None: return Repeat.Once;
        case Repeat.Once: return Repeat.Loop;
        case Repeat.Loop: return Repeat.None;
      }
    });
  }

  public clearTracks() {
    this.trackService.clearTracks();
    this.tracksCanBeRestored.set(true);
  }

  public restoreTracks() {
    this.trackService.restoreTracks();
    this.tracksCanBeRestored.set(false);
  }

  public showAlbumArt() {
    if (this.trackService.activeTrack) {
      let url = this.trackService.activeTrack?.coverURL;

      this.modalService.setDynamicModal(AlbumArtFullscreenComponent, [{
        name: 'url',
        val: url
      }], 'Album Art');
      this.modalService.show();
    } else {
      this.notificationService.info('You must have a track playing to view album art.');
    }
  }

  public get consumeState() {
    return this.trackService.consume.asReadonly();
  }

  showTrackInfo() {
    if (this.trackService.activeTrack) {
      this.modalService.setDynamicModal(TrackInfoComponent, [{
        name: 'trackInfo',
        val: this.trackService.activeTrack
      }], 'Track Info');
      this.modalService.show();
    } else {
      this.notificationService.info('You must have a track playing to view track info.');
    }
  }

  public showPlayerSettings() {
    this.modalService.setDynamicModal(PlayerSettingsComponent, [{
      'name': 'visualizerSignal',
      'val': this.showVisualizer
    }], 'Player Settings');
    this.modalService.show();
  }

  ngOnInit(): void {
    this.tracks.set(this.trackService.tracks);

    this.trackUpdated = this.playerService.trackUpdated.subscribe((val) => {
      this.title.set(val.title);
      this.artist.set(val.artist?.name || '');
      this.bg.set(val.cover);
    });
    this.stateChanged = this.playerService.stateChanged.subscribe((val) => {
      if (val == PlaybackState.Paused) {
        this.playing = false;
      } else {
        this.playing = true;
      }
    });
    this.playbackEnded = this.playerService.playbackEnded.subscribe(_ => {
      this.title.set('');
      this.artist.set('');
      this.bg.set("assets/img/osse.webp");
      this.playing = false;
    });

    // Get the initial value of the current track
    if (this.trackService.activeTrack) {
      this.title.set(this.trackService.activeTrack.title);
      this.artist.set(this.trackService.activeTrack.artistPrimary()?.name ?? '');
      this.bg.set(this.trackService.activeTrack.coverURL);
    }

    this.showVisualizer.set(this.configService.get('showVisualizer'));
  }

  ngOnDestroy(): void {
    this.trackUpdated.unsubscribe();
    this.stateChanged.unsubscribe();
    this.playbackEnded.unsubscribe();

    this.trackService.removeClearedTracks();
  }
}
