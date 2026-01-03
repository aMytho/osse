import { ChangeDetectionStrategy, Component, EventEmitter, Input, OnInit, Output, WritableSignal, signal } from '@angular/core';
import { Track } from '../../../services/track/track';
import { PlaylistService } from '../../../services/playlist/playlist.service';
import { Playlist } from '../../../services/playlist/Playlist';
import { FormsModule } from '@angular/forms';
import { ToastService } from '../../../../toast-container/toast.service';

@Component({
  selector: 'app-add-multiple-tracks-to-playlist',
  imports: [FormsModule],
  templateUrl: './add-multiple-tracks-to-playlist.component.html',
  styles: ``,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AddMultipleTracksToPlaylistComponent implements OnInit {
  @Input('tracks') tracks: Track[] = [];
  @Output('onClose') onClose = new EventEmitter();

  playlists: WritableSignal<Playlist[]> = signal([]);
  model: WritableSignal<number> = signal(-1);

  constructor(private playlistService: PlaylistService, private notificationService: ToastService) { }

  public close() {
    this.onClose.emit();
  }

  public async onSave() {
    await this.playlistService.addTracksToPlaylist(Number(this.model()), this.tracks.map((t) => t.id));
    this.notificationService.info(this.tracks.length + ' tracks added to playlist.');
    this.close();
  }

  async ngOnInit(): Promise<void> {
    this.playlists.set(await this.playlistService.getAll());
  }
}

