import { Component, computed } from '@angular/core';
import { PlayerService } from '../player.service';
import { getNicelyFormattedTime } from '../../util/time';

@Component({
  selector: 'app-duration',
  imports: [],
  templateUrl: './duration.component.html',
  styles: ``
})
export class DurationComponent {
  public duration = computed(() => {
    let currentTime = this.playerService.currentTime();
    let totalTime = this.playerService.duration();

    return getNicelyFormattedTime(currentTime) + ' / ' + getNicelyFormattedTime(totalTime);
  })


  constructor(private playerService: PlayerService) { }
}
