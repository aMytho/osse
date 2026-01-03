import { Component, EventEmitter, Input, Output, signal, WritableSignal } from '@angular/core';
import { Track } from '../../../services/track/track';
import { Playlist } from '../../../services/playlist/Playlist';
import { PlaylistService } from '../../../services/playlist/playlist.service';

import { FormsModule } from '@angular/forms';
import { OssePlaylist } from '../../../services/playlist/osse-playlist';
import { ToastService } from '../../../../toast-container/toast.service';

@Component({
  selector: 'app-create-new-playlist-for-tracks',
  imports: [FormsModule],
  templateUrl: './create-new-playlist-for-tracks.component.html',
  styles: ``
})
export class CreateNewPlaylistForTracksComponent {
  @Input('tracks') tracks!: Track[];
  @Output('onClose') onClose = new EventEmitter();
  playlists: WritableSignal<Playlist[]> = signal([]);
  playlistModel: WritableSignal<string> = signal("");

  constructor(private playlistService: PlaylistService, private notificationService: ToastService) { }

  public close() {
    this.onClose.emit();
  }

  public async onSave() {
    let playlistId = 0;
    try {
      let req = await this.playlistService.createPlaylist(this.playlistModel());
      let res: OssePlaylist = await req.json();
      playlistId = res.id;
    } catch (error) {
      this.notificationService.error('Failed to create playlist. Check that the name is not already in use.')
      return;
    }

    try {
      await this.playlistService.addTracksToPlaylist(playlistId, this.tracks.map((t) => t.id));
      this.notificationService.info(`Playlist created. ${this.tracks.length} tracks added to playlist.`);
    } catch (error) {
      this.notificationService.error('The playlist was created, but the tracks were not added. Try again?')
    }

    this.close();
  }

  async ngOnInit(): Promise<void> {
    this.playlists.set(await this.playlistService.getAll());
  }
}
