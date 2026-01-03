import { Component, ElementRef, input, output, signal, ViewChild, WritableSignal } from '@angular/core';
import { Track } from '../../../shared/services/track/track';
import { TrackMatrixComponent } from '../../../shared/ui/track-matrix/track-matrix.component';
import { HeaderComponent } from '../../../shared/ui/header/header.component';
import { CommonModule } from '@angular/common';
import { IconComponent } from '../../../shared/ui/icon/icon.component';
import { debounceTime, fromEvent, Subscription } from 'rxjs';
import { TrackMatrixMode } from '../../../shared/ui/track-matrix/track-matrix-mode.enum';
import { fetcher } from '../../../shared/util/fetcher';
import { mdiClose, mdiPlaylistPlay } from '@mdi/js';

@Component({
  selector: 'app-playlist-add-tracks',
  imports: [CommonModule, TrackMatrixComponent, HeaderComponent, IconComponent],
  templateUrl: './playlist-add-tracks.component.html',
  styles: ``
})
export class PlaylistAddTracksComponent {
  @ViewChild('search') searchBar!: ElementRef;
  @ViewChild(TrackMatrixComponent) matrix!: TrackMatrixComponent;

  public playlistID = input.required<number>({});
  public addTracks = output<Track[]>();
  public loading: WritableSignal<boolean> = signal(true);
  public waitingOnRequest: WritableSignal<boolean> = signal(false);
  public tracks: WritableSignal<Track[]> = signal([]);
  private allTracks: Track[] = [];
  private timeout: number = 0;
  private scrollSubscription!: Subscription;

  playlist = mdiPlaylistPlay;
  close = mdiClose;

  constructor() { }

  ngAfterViewInit(): void {
    this.matrix.setMode(TrackMatrixMode.Select);
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

  public async addSelectedTracksToPlaylist() {
    let tracks = this.matrix.getSelectedTracks();

    if (tracks.length > 0) {
      this.waitingOnRequest.set(true);
      let req = await fetcher(`playlists/${this.playlistID()}/track-set`, {
        method: 'POST',
        body: JSON.stringify({
          'track-ids': tracks.map((t) => t.id)
        })
      });

      // If success, add tracks to above UI
      if (req.ok) {
        console.log(tracks);
        this.addTracks.emit(tracks);
        this.matrix.clearSelectedTracks();
      }

      this.waitingOnRequest.set(false);
    }
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
}
