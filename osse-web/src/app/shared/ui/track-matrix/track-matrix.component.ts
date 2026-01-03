import { Component, ViewChildren, WritableSignal, input, output, signal } from '@angular/core';
import { Track } from '../../services/track/track';
import { MatrixItemComponent } from './matrix-item/matrix-item.component';
import { TrackMatrixMode } from './track-matrix-mode.enum';
import { TrackField, TrackInfo } from './track-info';

@Component({
  selector: 'app-track-matrix',
  imports: [MatrixItemComponent],
  templateUrl: './track-matrix.component.html',
  styles: ``
})
export class TrackMatrixComponent {
  @ViewChildren(MatrixItemComponent) items!: MatrixItemComponent[];
  public tracks = input<Track[]>([]);

  public selectedTracks: Track[] = [];
  public mode: WritableSignal<TrackMatrixMode> = signal<TrackMatrixMode>(TrackMatrixMode.View);
  public visibleTrackInfo: WritableSignal<TrackField[]> = signal(TrackInfo.default());

  public onClick = output<Track>();
  public onModeChanged = output<TrackMatrixMode>();
  public onEmptySelection = output(); public onTrackSelected = output<Track>();

  public toggleSelect(selected: boolean, track: Track) {
    if (selected) {
      // If a CTRL click happened and the mode is view, switch to edit and select it.
      if (this.mode() == TrackMatrixMode.View) {
        this.setMode(TrackMatrixMode.Select);
      }

      this.selectedTracks.push(track);
      this.onTrackSelected.emit(track);
    } else {
      this.selectedTracks = this.selectedTracks.filter((t) => t.uuid != track.uuid);
    }

    this.checkForEmptySelection();
  }

  public setMode(mode: TrackMatrixMode) {
    this.mode.set(mode);
    this.onModeChanged.emit(this.mode());
  }

  public clearSelectedTracks() {
    this.selectedTracks = [];
    this.items.forEach((i) => i.setSelected(false));
    this.onEmptySelection.emit();
  }

  private checkForEmptySelection() {
    if (this.selectedTracks.length == 0) {
      this.onEmptySelection.emit();
    }
  }

  public getSelectedTracks() {
    return this.selectedTracks;
  }

  public setVisibleFields(fields: TrackField[]) {
    this.visibleTrackInfo.set(fields);
  }
}
