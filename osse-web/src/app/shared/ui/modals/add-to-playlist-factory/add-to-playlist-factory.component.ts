import { Component, EventEmitter, Input, OnInit, Output, signal } from '@angular/core';
import { Track } from '../../../services/track/track';
import { PlaylistService } from '../../../services/playlist/playlist.service';
import { Playlist } from '../../../services/playlist/Playlist';
import { AddMultipleTracksToPlaylistComponent } from "../add-multiple-tracks-to-playlist/add-multiple-tracks-to-playlist.component";
import { CreateNewPlaylistForTracksComponent } from "../create-new-playlist-for-tracks/create-new-playlist-for-tracks.component";

@Component({
  selector: 'app-add-to-playlist-factory',
  imports: [AddMultipleTracksToPlaylistComponent, CreateNewPlaylistForTracksComponent],
  templateUrl: './add-to-playlist-factory.component.html',
  styles: ``
})
export class AddToPlaylistFactoryComponent implements OnInit {
  @Input('tracks') tracks: Track[] = [];
  @Output('onClose') onClose = new EventEmitter();

  loadingPlaylists = signal(true);
  playlists = signal<Playlist[]>([]);

  constructor(private playlistService: PlaylistService) { }

  public close() {
    this.onClose.emit();
  }

  async ngOnInit(): Promise<void> {
    this.loadingPlaylists.set(true);
    this.playlists.set(await this.playlistService.getAll());
    this.loadingPlaylists.set(false);
  }
}
