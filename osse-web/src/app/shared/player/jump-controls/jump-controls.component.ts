import { Component } from '@angular/core';
import { PlayerService } from '../player.service';
import { IconComponent } from '../../ui/icon/icon.component';
import { mdiFastForward10, mdiFastForward30, mdiRewind10, mdiRewind30 } from '@mdi/js';
import { TrackService } from '../../services/track/track.service';

@Component({
  selector: 'app-jump-controls',
  imports: [IconComponent],
  templateUrl: './jump-controls.component.html',
  styles: ``
})
export class JumpControlsComponent {
  constructor(private trackService: TrackService, public playerService: PlayerService) { }

  back10 = mdiRewind10;
  back30 = mdiRewind30;
  forward10 = mdiFastForward10;
  forward30 = mdiFastForward30;

  public jump(duration: number, jumpForward: boolean) {
    if (this.trackService.activeTrack) {
      this.playerService.jumpDuration(duration, jumpForward);
    }
  }
}
