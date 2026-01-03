import { AfterViewInit, ChangeDetectionStrategy, Component, ElementRef, OnDestroy, OnInit, ViewChild, WritableSignal, signal } from '@angular/core';
import { TrackService } from '../shared/services/track/track.service';
import { HeaderComponent } from '../shared/ui/header/header.component';
import { Track } from '../shared/services/track/track';
import { ToastService } from '../toast-container/toast.service';
import { fetcher } from '../shared/util/fetcher';
import { debounceTime, fromEvent, Subscription } from 'rxjs';
import { TrackMatrixComponent } from '../shared/ui/track-matrix/track-matrix.component';
import { TrackMatrixMode } from '../shared/ui/track-matrix/track-matrix-mode.enum'; import { IconComponent } from "../shared/ui/icon/icon.component";
import { mdiClose, mdiPencil, mdiPlaylistPlay } from '@mdi/js';
import { CommonModule } from '@angular/common';
import { ModalService } from '../shared/ui/modal/modal.service';
import { AddToPlaylistFactoryComponent } from '../shared/ui/modals/add-to-playlist-factory/add-to-playlist-factory.component';

@Component({
  selector: 'app-track-list',
  templateUrl: './track-list.component.html',
  styles: ``,
  imports: [HeaderComponent, TrackMatrixComponent, IconComponent, CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TrackListComponent implements AfterViewInit, OnInit, OnDestroy {
  @ViewChild('search') searchBar!: ElementRef;
  @ViewChild(TrackMatrixComponent) matrix!: TrackMatrixComponent;

  public loading: WritableSignal<boolean> = signal(true);
  public tracks: WritableSignal<Track[]> = signal([]);
  public editing: WritableSignal<boolean> = signal(false);
  private allTracks: Track[] = [];
  private timeout: number = 0;
  private scrollSubscription!: Subscription;

  pencil = mdiPencil;
  close = mdiClose;
  play = mdiPlaylistPlay;

  constructor(
    private trackService: TrackService,
    private notificationService: ToastService,
    private modalService: ModalService
  ) { }

  ngAfterViewInit(): void {
    this.searchBar.nativeElement.focus();
  }

  async ngOnInit(): Promise<void> {
    // Listen for scroll events
    this.scrollSubscription = fromEvent(window, 'scroll')
      .pipe(debounceTime(300))
      .subscribe(() => {
        const endOfPage = window.innerHeight + window.pageYOffset >= (document.body.offsetHeight * 0.6);
        if (endOfPage) {
          this.requestTracks(this.searchBar.nativeElement.value);
        }
      })

    // On load, get the first 75 tracks
    let req = await fetcher('tracks/search');
    this.loading.set(false);
    if (!req.ok) return;

    let tracks = await req.json();
    tracks.forEach((track: any) => {
      this.allTracks.push(new Track(track));
    });
    this.tracks.set(this.allTracks);
  }

  public onSubmit() {
    for (let track of this.tracks()) {
      this.trackService.addTrack(track);
    }
    this.notificationService.info('Added ' + this.tracks().length + ' tracks');
  }

  public addTrack(track: Track) {
    this.trackService.addTrack(track);
    this.notificationService.info('Added ' + track.title);
  }

  public playSelectedTracks() {
    let tracks = this.matrix.getSelectedTracks();
    for (const track of tracks) {
      this.trackService.addTrack(track);
    }

    this.notificationService.info('Added ' + tracks.length + ' tracks.');
  }

  public addSelectedTracksToPlaylist() {
    this.modalService.setDynamicModal(AddToPlaylistFactoryComponent, [{
      name: 'tracks',
      val: this.matrix.getSelectedTracks()
    }], 'Add to Playlist');
    this.modalService.show();
  }

  public async onInput(ev: any) {
    // Search for tracks. We made a debounce which waits 500ms before sending.
    // Makes it a little easier on the server.

    // If the search input is empty, reset the filter
    if (ev.target.value.length == 0) {
      this.tracks.set(this.allTracks);
    }

    clearTimeout(this.timeout);
    this.timeout = setTimeout(async () => {
      // Don't search for empty string
      if (ev.target.value.trim() == '') return;
      this.requestTracks(ev.target.value.trim());
    }, 500);
  }

  public async requestTracks(search: string) {
    // Find the amount of track that we have that match the regex.
    let offset = 0;

    if (search.length == 0) {
      offset = this.tracks().length;
    } else {
      const regex = new RegExp('%' + search + "%");
      this.tracks().forEach(val => {
        if (regex.test(val.title)) {
          offset += 1;
        }
      });
      if (offset < 75) {
        offset = 0;
      }
    }

    // Search for tracks
    this.loading.set(true);
    let req = await fetcher('tracks/search?'
      + new URLSearchParams({
        track: search,
        track_offset: offset.toString()
      }).toString());
    this.loading.set(false);
    if (!req.ok && req.status == 200) return;

    let json = await req.json();
    for (let track of json) {
      if (this.allTracks.some(v => v.id == track.id)) continue;
      this.allTracks.push(new Track(track));
    }
    this.tracks.set(this.getMatchingTracks(search));
  }

  public getMatchingTracks(search: string): Track[] {
    let regex = new RegExp(search, 'i');
    return this.allTracks.filter((v) => regex.test(v.track.title));
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

  ngOnDestroy(): void {
    if (this.scrollSubscription) {
      this.scrollSubscription.unsubscribe();
    }
  }
}
