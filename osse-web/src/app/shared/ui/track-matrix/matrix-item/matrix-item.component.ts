import { Component, input, InputSignal, output, signal, WritableSignal } from '@angular/core';
import { Track } from '../../../services/track/track';
import { TrackMatrixMode } from '../track-matrix-mode.enum';
import { CommonModule } from '@angular/common';
import { TrackField, TrackInfo } from '../track-info';

@Component({
  selector: 'app-matrix-item',
  imports: [CommonModule],
  templateUrl: './matrix-item.component.html',
  styles: ``
})
export class MatrixItemComponent {
  public track = input.required<Track>();
  public TrackMatrixMode = TrackMatrixMode;
  public mode: InputSignal<TrackMatrixMode> = input<TrackMatrixMode>(TrackMatrixMode.View);
  public selected: WritableSignal<boolean> = signal(false);
  public visibleTrackInfo = input<TrackField[]>();
  public trackInfo = TrackInfo;
  public trackField = TrackField;

  public onClick = output();
  public onSelectToggle = output<boolean>();

  public toggleSelected() {
    this.selected.set(!this.selected());
    this.onSelectToggle.emit(this.selected());
  }

  public determineClickTypeAndEmitEvent(event: MouseEvent) {
    if (event.ctrlKey) {
      this.toggleSelected();
    } else {
      this.onClick.emit();
    }
  }

  public emitOnClickEvent() {
    this.onClick.emit();
  }

  public setSelected(selected: boolean) {
    this.selected.set(selected);
  }
}
