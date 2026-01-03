import { AfterViewInit, Component, ElementRef, ViewChild } from '@angular/core';
import { mdiRestart } from '@mdi/js';
import { PlayerService } from '../player.service';
import { IconComponent } from '../../ui/icon/icon.component';

@Component({
  selector: 'app-speed-controls',
  imports: [IconComponent],
  templateUrl: './speed-controls.component.html',
  styles: ``
})
export class SpeedControlsComponent implements AfterViewInit {
  @ViewChild('speed') speedInput!: ElementRef<HTMLInputElement>;
  reset = mdiRestart;

  constructor(private playerService: PlayerService) { }

  setInitialSpeed(): void {
    this.storeAndSetSpeed(Number(localStorage.getItem('speed') ?? 1));
    this.speedInput.nativeElement.value = String(this.playerService.getSpeed());
  }

  onSpeedChange(event: any) {
    this.storeAndSetSpeed(event.target.value);
  }

  adjustSpeedByScroll(event: any) {
    event.preventDefault();
    let currentSpeed = this.playerService.getSpeed();

    let newSpeed; if (event.deltaY > 0) {
      newSpeed = Math.max(0, currentSpeed - 0.1);
    } else {
      newSpeed = Math.min(2, currentSpeed + 0.1);
    }

    this.speedInput.nativeElement.value = String(newSpeed);
    this.storeAndSetSpeed(newSpeed);
  }

  onSpeedReset() {
    this.speedInput.nativeElement.value = "1";
    // The event isn't triggered.
    this.storeAndSetSpeed(1);
  }

  private storeAndSetSpeed(speed: number) {
    this.playerService.setSpeed(speed);
    localStorage.setItem('speed', speed.toString());
  }

  ngAfterViewInit(): void {
    this.setInitialSpeed();
  }
}
