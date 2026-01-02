import { ChangeDetectionStrategy, Component, OnInit, WritableSignal, signal } from '@angular/core';
import { ConfigService } from '../shared/services/config/config.service';
import { RouterLink } from '@angular/router';
import { Album } from '../shared/services/album/Album';
import { HeaderComponent } from '../shared/ui/header/header.component';
import { OsseAlbum } from '../shared/services/album/osse-album';
import { fetcher } from '../shared/util/fetcher';
import { IconComponent } from '../shared/ui/icon/icon.component';
import { mdiPlaylistPlay, mdiSearchWeb } from '@mdi/js';
import { TrackService } from '../shared/services/track/track.service';
import { ToastService } from '../toast-container/toast.service';

@Component({
  selector: 'app-albums',
  imports: [RouterLink, HeaderComponent, IconComponent],
  templateUrl: './albums.component.html',
  styleUrl: `./albums.component.css`,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AlbumsComponent implements OnInit {
  albums: WritableSignal<Album[]> = signal([]);
  filteredAlbums: WritableSignal<Album[]> = signal([]);
  coverUrlBase: WritableSignal<string> = signal(this.configService.get('apiURL') + "tracks/ID/cover");
  loading: WritableSignal<boolean> = signal(true);

  search = mdiSearchWeb;
  play = mdiPlaylistPlay;

  constructor(
    private configService: ConfigService,
    private trackService: TrackService,
    private notificationService: ToastService
  ) { }

  public filterAlbums(event: any) {
    if (event.target.value.trim().length == 0) {
      this.filteredAlbums.set(this.albums());
    } else {
      const regex = new RegExp(event.target.value.trim(), 'i');
      this.filteredAlbums.set(this.albums().filter((a) => regex.test(a.name)));
    }
  }

  public playAlbum(id: number) {
    let album = this.filteredAlbums().find((a) => a.id == id);
    if (album) {
      for (const track of album.tracks) {
        this.trackService.addTrack(track);
      }

      if (album.tracks.length > 1) {
        this.notificationService.info(`Added ${album.tracks.length} tracks to queue.`);
      } else {
        this.notificationService.info(`Added track to queue.`);
      }
    }
  }

  async ngOnInit(): Promise<void> {
    let request = await fetcher('albums?tracks=true');
    let result: OsseAlbum[] = await request.json();

    result.sort((a, b) => a.name.localeCompare(b.name));
    this.albums.set(result.map((a) => new Album(a)));
    this.filteredAlbums.set(this.albums());
    this.loading.set(false);
  }
}
