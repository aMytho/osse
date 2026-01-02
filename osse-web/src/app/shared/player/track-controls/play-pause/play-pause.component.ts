import { Component, computed } from '@angular/core';
import { TrackService } from '../../../services/track/track.service';
import { PlayerService } from '../../player.service';
import { IconComponent } from '../../../ui/icon/icon.component';
import { mdiPause, mdiPlay } from '@mdi/js';

@Component({
  selector: 'app-play-pause',
  imports: [IconComponent],
  templateUrl: './play-pause.component.html',
  styles: ``
})
export class PlayPauseComponent {
  public playerIcon = computed(() => this.playerService.isPlaying() ? mdiPause : mdiPlay);

  constructor(private trackService: TrackService, private playerService: PlayerService) { }
  public onPlayerToggle() {
    // If no track, don't respond to button click
    if (!this.trackService.activeTrack) return;

    this.playerService.toggle();
  }
}
