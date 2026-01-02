import { Component, computed, Input, numberAttribute, signal, ViewChild, WritableSignal } from '@angular/core';
import { Playlist } from '../../shared/services/playlist/Playlist';
import { HeaderComponent } from '../../shared/ui/header/header.component';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { EditPlaylist } from './editPlaylistModel';
import { FormsModule } from '@angular/forms';
import { IconComponent } from '../../shared/ui/icon/icon.component';
import { mdiClose, mdiPencil, mdiPlaylistPlay, mdiTrashCan } from '@mdi/js';
import { PlaylistService } from '../../shared/services/playlist/playlist.service';
import { TrackService } from '../../shared/services/track/track.service';
import { ToastService } from '../../toast-container/toast.service';
import { Track } from '../../shared/services/track/track';
import { fetcher } from '../../shared/util/fetcher';
import { TrackMatrixComponent } from '../../shared/ui/track-matrix/track-matrix.component';
import { TrackMatrixMode } from '../../shared/ui/track-matrix/track-matrix-mode.enum';
import { PlaylistAddTracksComponent } from './playlist-add-tracks/playlist-add-tracks.component';

@Component({
  selector: 'app-playlist-view',
  imports: [HeaderComponent, IconComponent, CommonModule, FormsModule, TrackMatrixComponent, PlaylistAddTracksComponent],
  templateUrl: './playlist-view.component.html',
  styles: ``
})
export class PlaylistViewComponent {
  @ViewChild(TrackMatrixComponent) tracks!: TrackMatrixComponent;
  @Input({ transform: numberAttribute })
  set id(id: number) {
    this.getPlaylist(id);
  }

  pencil = mdiPencil;
  trash = mdiTrashCan;
  play = mdiPlaylistPlay;
  close = mdiClose;

  public playlist = signal<Playlist|null>(null);
  public showTrackSelectionMenu: WritableSignal<boolean> = signal(false);
  public ready: WritableSignal<boolean> = signal(false);
  public waitingOnRequest: WritableSignal<boolean> = signal(false);
  public activeTab = signal('view');
  public showViewTab = computed(() => this.activeTab() == 'view');
  public showAddTracksTab = computed(() => this.activeTab() == 'addTracks');
  public showModifyTab = computed(() => this.activeTab() == 'modify');
  public model = new EditPlaylist('');

  constructor(
    private router: Router,
    private playlistService: PlaylistService,
    private trackService: TrackService,
    private notificationService: ToastService
  ) { }

  public delete() {
    if (confirm(`Are you sure you want to delete ${this.playlist()!.name}?`)) {
      fetcher('playlists/' + this.playlist()?.id, {
        method: 'DELETE',
      }).then((_r) => {
        this.router.navigate(['/playlists']);
      })
    }
  }

  public async edit() {
    let req = await fetcher('playlists/' + this.playlist()?.id, {
      method: 'PATCH',
      body: JSON.stringify({
        name: this.model.name
      }),
      headers: [
        ['Content-Type', 'application/json']
      ]
    });

    if (req.ok) {
      this.getPlaylist(this.playlist()?.id as number);
      this.notificationService.info('Playlist renamed successfully.');
      this.activeTab.set('view');
    }
  }

  public addTrack(track: Track) {
    this.trackService.addTrack(track);
    this.notificationService.info('Added ' + track.title);
  }

  public addTracksToQueue() {
    let tracks = this.playlist()!.tracks;
    for (let track of tracks) {
      this.trackService.addTrack(track);
    }

    this.notificationService.info('Added ' + tracks.length + ' tracks');
  }

  private async getPlaylist(id: number) {
    this.playlist.set(await this.playlistService.getPlaylist(id));
    this.model.name = this.playlist()?.name ?? '';
    this.ready.set(true);
  }

  public onTrackMatrixModeChange(mode: TrackMatrixMode) {
    this.showTrackSelectionMenu.set(mode == TrackMatrixMode.Select);
  }

  public closePlaylistTrackSelector() {
    this.tracks.setMode(TrackMatrixMode.View);
    this.tracks.clearSelectedTracks();
  }

  public async removeTracksFromPlaylist() {
    let tracks = this.tracks.getSelectedTracks();
    if (tracks.length == 0) {
      return;
    }

    this.waitingOnRequest.set(true);
    let req = await fetcher(`playlists/${this.playlist()?.id}/track-set`, {
      method: 'DELETE',
      body: JSON.stringify({
        'track-ids': tracks.map((t) => t.id)
      })
    });

    if (req.ok) {
      this.playlist.update((p) => {
        p!.tracks = p!.tracks.filter((t) => !tracks.some((t2) => t2.id == t.id))
        return p;
      });

      this.notificationService.info('Removed ' + tracks.length + ' tracks from ' + this.playlist()!.name);
      this.tracks.clearSelectedTracks();
      this.tracks.setMode(TrackMatrixMode.View);
    }

    this.waitingOnRequest.set(false);
  }

  addTracksToPlaylist(tracks: Track[]) {
    this.playlist.update((p) => {
      p!.tracks.push(...tracks);
      return p;
    })
    this.notificationService.info('Added ' + tracks.length + ' tracks to ' + this.playlist()!.name);
    this.activeTab.set('view');
    this.tracks.clearSelectedTracks();
    this.tracks.setMode(TrackMatrixMode.View);
  }

  playAll() {
    let tracks = this.tracks.tracks();
    tracks.forEach((t) => this.trackService.addTrack(t));
    this.notificationService.info('Added ' + tracks.length + ' tracks.');
  }
}

