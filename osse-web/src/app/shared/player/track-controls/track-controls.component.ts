import { Component } from '@angular/core';
import { IconComponent } from '../../ui/icon/icon.component';
import { mdiFastForward, mdiRewind } from '@mdi/js';
import { TrackService } from '../../services/track/track.service';
import { PlayPauseComponent } from './play-pause/play-pause.component';

@Component({
  selector: 'app-track-controls',
  imports: [IconComponent, PlayPauseComponent],
  templateUrl: './track-controls.component.html',
  styles: ``
})
export class TrackControlsComponent {
  forward = mdiFastForward;
  back = mdiRewind;

  constructor(private trackService: TrackService) { }

  public onNextTrack() {
    this.trackService.moveToNextTrack();
  }

  public onPreviousTrack() {
    this.trackService.moveToLastTrack();
  }
}
