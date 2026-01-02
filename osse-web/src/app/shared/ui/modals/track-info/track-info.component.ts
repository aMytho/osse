import { Component, EventEmitter, Input, Output } from '@angular/core';
import { Track } from '../../../services/track/track';

@Component({
  selector: 'app-track-info',
  imports: [],
  templateUrl: './track-info.component.html',
  styles: ``
})
export class TrackInfoComponent {
  @Input()
  public trackInfo!: Track;
  @Output() onClose = new EventEmitter();

  public close() {
    this.onClose.emit();
  }
}
