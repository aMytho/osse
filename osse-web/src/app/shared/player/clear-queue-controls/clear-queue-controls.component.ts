import { Component } from '@angular/core';
import { TrackService } from '../../services/track/track.service';
import { mdiDeleteSweep } from '@mdi/js';
import { IconComponent } from '../../ui/icon/icon.component';

@Component({
  selector: 'app-clear-queue-controls',
  imports: [IconComponent],
  templateUrl: './clear-queue-controls.component.html',
  styles: ``
})
export class ClearQueueControlsComponent {
  constructor(private trackService: TrackService) { }

  clear = mdiDeleteSweep;

  clearQueue() {
    this.trackService.clearTracks();
  }
}
