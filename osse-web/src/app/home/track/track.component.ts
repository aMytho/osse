import { Component, EventEmitter, input, Input, InputSignal, Output } from '@angular/core';
import { Track } from '../../shared/services/track/track';
import { IconComponent } from '../../shared/ui/icon/icon.component';
import { mdiClose, mdiDotsVertical, mdiPlay, mdiPlaylistPlus, mdiStar, mdiTrashCan } from '@mdi/js';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-track',
  imports: [IconComponent, CommonModule],
  templateUrl: './track.component.html'
})
export class TrackComponent {
  @Input() track!: Track;
  public activeTrack: InputSignal<boolean> = input<boolean>(false);
  @Output() onPlay = new EventEmitter();
  @Output() onRemove = new EventEmitter();
  @Output() onPlaylistAdd = new EventEmitter<Track>();
  public mode: string = 'view';

  star = mdiStar;
  dots = mdiDotsVertical;
  trash = mdiTrashCan;
  cancel = mdiClose;
  playlist = mdiPlaylistPlus;
  play = mdiPlay;

  public toggleView() {
    if (this.mode == 'view') {
      this.mode = 'act';
    } else {
      this.mode = 'view';
    }
  }

  public toggleViewWithEvent(ev: Event) {
    ev.preventDefault();
    this.toggleView();
  }

  public removeTrack() {
    this.onRemove.emit();
    this.toggleView();
  }

  public addToPlaylist() {
    this.onPlaylistAdd.emit(this.track);
    this.toggleView();
  }

  public playTrack() {
    this.onPlay.emit();
    this.toggleView();
  }
}

