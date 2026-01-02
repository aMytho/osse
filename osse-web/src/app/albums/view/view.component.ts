import { AfterViewInit, ChangeDetectionStrategy, Component, OnDestroy, OnInit, ViewChild, WritableSignal, signal } from '@angular/core';
import { Album } from '../../shared/services/album/Album';
import { ConfigService } from '../../shared/services/config/config.service';
import { TrackService } from '../../shared/services/track/track.service';
import { Track } from '../../shared/services/track/track';
import { HeaderComponent } from '../../shared/ui/header/header.component';
import { ToastService } from '../../toast-container/toast.service';
import { BackgroundImageService } from '../../shared/ui/background-image.service';
import { IconComponent } from '../../shared/ui/icon/icon.component';
import { mdiClose, mdiFilter, mdiPencil, mdiPlaylistPlay, mdiSearchWeb } from '@mdi/js';
import { AlbumFilter } from './album-filter';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { ModalService } from '../../shared/ui/modal/modal.service';
import { AlbumArtFullscreenComponent } from '../../shared/ui/modals/album-art-fullscreen/album-art-fullscreen.component';
import { TrackMatrixComponent } from '../../shared/ui/track-matrix/track-matrix.component';
import { CommonModule } from '@angular/common';
import { TrackMatrixMode } from '../../shared/ui/track-matrix/track-matrix-mode.enum';
import { AddMultipleTracksToPlaylistComponent } from '../../shared/ui/modals/add-multiple-tracks-to-playlist/add-multiple-tracks-to-playlist.component';
import { Subscription } from 'rxjs';
import { TrackInfo } from '../../shared/ui/track-matrix/track-info';

@Component({
  selector: 'app-view',
  imports: [HeaderComponent, IconComponent, FormsModule, TrackMatrixComponent, CommonModule],
  templateUrl: './view.component.html',
  styles: ``,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ViewComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild(TrackMatrixComponent) matrix!: TrackMatrixComponent;
  public album!: WritableSignal<Album>;
  public filteredTracks: WritableSignal<Track[]> = signal([]);
  public filterType = AlbumFilter;
  public chosenFilter: WritableSignal<AlbumFilter> = signal(AlbumFilter.TrackNumber);
  public albumTrackArtist: WritableSignal<string> = signal('');
  public bg = signal('');
  public duration = 0;
  public editing: WritableSignal<boolean> = signal(false);

  search = mdiSearchWeb;
  filter = mdiFilter;
  pencil = mdiPencil;
  close = mdiClose;
  play = mdiPlaylistPlay;

  private modalSubscription!: Subscription;

  constructor(
    private configService: ConfigService,
    private trackService: TrackService,
    private notificationService: ToastService,
    private backgroundImageService: BackgroundImageService,
    private activatedRoute: ActivatedRoute,
    private modalService: ModalService
  ) { }

  public addAll() {
    this.album().tracks.forEach((t) => this.trackService.addTrack(t));
    this.notificationService.info('Added ' + this.album().tracks.length + ' tracks');
  }

  public addTrack(track: Track) {
    this.trackService.addTrack(track);
    this.notificationService.info('Added ' + track.title);
  }

  public get totalDuration() {
    let total = 0;

    this.album().tracks.forEach(t => {
      total += t.duration;
    });

    return Math.floor(total / 60);
  }

  public filterTracks(event: any) {
    if (event.target.value.trim().length == 0) {
      this.filteredTracks.set(this.album().tracks);
    } else {
      const regex = new RegExp(event.target.value, 'i');
      this.filteredTracks.set(this.album().tracks.filter((t) => regex.test(t.title)));
    }
  }

  public sortTracks() {
    if (this.chosenFilter() == AlbumFilter.Alphabetical) {
      this.filteredTracks().sort((a, b) => {
        if (a.title.toLowerCase() < b.title.toLowerCase()) {
          return -1;
        }
        if (a.title.toLowerCase() > b.title.toLowerCase()) {
          return 1;
        }
        return 0;
      });
    } else if (this.chosenFilter() == AlbumFilter.Random) {
      this.filteredTracks.update(value => {
        return value.map(value => ({ value, sort: Math.random() }))
          .sort((a, b) => a.sort - b.sort)
          .map(({ value }) => value)
      });
    } else if (this.chosenFilter() == AlbumFilter.DiscNumber) {
      this.filteredTracks.update(v => v.sort((a, b) => (a.discNumber ?? 0) - (b.discNumber ?? 0)));
      // To-do: When we store the total discs, sort by disc order and show title
    } else {
      // Sort by track number
      this.filteredTracks.update(v => v.sort((a, b) => (a.trackNumber ?? 0) - (b.trackNumber ?? 0)));
    }
  }

  public async artistFromTracks() {
    let artists = new Map();
    for (let i = 0; i < this.album().tracks.length; i++) {
      let track = this.album().tracks[i];
      if (track.artistPrimary() != null) {
        artists.set(i, (artists.get(track.artistPrimary()?.id) ?? 0) + 1);
      }
    }

    if (artists.size == 0) return;

    // Get the artist with the highest track occurence and set it to the album artist
    let artist = ([...artists.entries()]).reduce((a, e) => e[1] > a[1] ? e : a);
    await this.album().tracks[artist[0]].getArtist();
    this.albumTrackArtist.set((this.album().tracks[artist[0]].artistPrimary())?.name + ' (Inferred)');
  }

  public showAlbumArt() {
    let url = this.album().tracks[0]?.coverURL;

    this.modalService.setDynamicModal(AlbumArtFullscreenComponent, [{
      name: 'url',
      val: url
    }], 'Album Art');
    this.modalService.show();
  }

  public handleModeChange(mode: TrackMatrixMode) {
    if (mode == TrackMatrixMode.Select) {
      this.editing.set(true);
    } else {
      this.editing.set(false);
    }
  }

  public handleEmptySelection() {
    this.matrix.setMode(TrackMatrixMode.View);
  }

  /**
  * Removes all selected tracks.
  */
  public clearSelectedTracks() {
    this.matrix.clearSelectedTracks();
  }

  public playSelectedTracks() {
    let tracks = this.matrix.getSelectedTracks();
    for (const track of tracks) {
      this.trackService.addTrack(track);
    }

    this.notificationService.info('Added ' + tracks.length + ' tracks.');
  }

  public addSelectedTracksToPlaylist() {
    this.modalService.setDynamicModal(AddMultipleTracksToPlaylistComponent, [{
      name: 'tracks',
      val: this.matrix.getSelectedTracks()
    }], 'Add to Playlist');
    this.modalService.show();
  }

  ngOnInit(): void {
    this.album = signal(this.activatedRoute.snapshot.data['album']);
    this.filteredTracks.set(this.album().tracks);
    this.bg.set(this.configService.get('apiURL') + "api/tracks/" + (this.album().tracks[0]?.id ?? -1) + '/cover')

    this.backgroundImageService.setBG(this.bg());

    this.sortTracks();
    this.artistFromTracks();

    this.duration = this.album().tracks.reduce((acc, t) => t.duration + acc, 0) % 60;

    this.modalSubscription = this.modalService.onClose.subscribe((_) => {
      this.clearSelectedTracks();
    });
  }

  ngAfterViewInit(): void {
    this.matrix.setVisibleFields(TrackInfo.allFields());
  }

  public ngOnDestroy(): void {
    this.modalSubscription.unsubscribe();
  }
}

