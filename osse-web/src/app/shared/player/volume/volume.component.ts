import { AfterViewInit, ChangeDetectionStrategy, Component, ElementRef, ViewChild, WritableSignal, signal } from '@angular/core';
import { PlayerService } from '../player.service';
import { mdiVolumeOff, mdiVolumeLow, mdiVolumeHigh } from '@mdi/js';
import { IconComponent } from '../../ui/icon/icon.component';


@Component({
  selector: 'app-volume',
  imports: [IconComponent],
  templateUrl: './volume.component.html',
  styles: ``,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class VolumeComponent implements AfterViewInit {
  @ViewChild('volume') volumeInput!: ElementRef<HTMLInputElement>;
  volumeIcon = signal(mdiVolumeHigh);
  public showVolumeMenu: WritableSignal<boolean> = signal(false);

  constructor(private playerService: PlayerService) { }

  setInitialVolume(): void {
    this.storeAndSetVolume(Number(localStorage.getItem('volume') ?? 1));
    this.volumeInput.nativeElement.value = String(this.playerService.getVolume());
  }

  onVolumeChange(event: any) {
    this.storeAndSetVolume(event.target.value);
  }

  adjustVolumeByScroll(event: any) {
    event.preventDefault();
    let currentVolume = this.playerService.getVolume();

    let newVolume;
    if (event.deltaY > 0) {
      newVolume = Math.max(0, currentVolume - 0.05);
    } else {
      newVolume = Math.min(1, currentVolume + 0.05);
    }

    this.volumeInput.nativeElement.value = String(newVolume);
    this.storeAndSetVolume(newVolume);
    this.setVolumeIcon();
  }

  onVolumeSet() {
    this.setVolumeIcon();
    this.showVolumeMenu.set(false);
  }

  setVolumeIcon() {
    let volume = this.playerService.getVolume();
    if (volume == 0) {
      this.volumeIcon.set(mdiVolumeOff);
    } else {
      if (volume <= 0.5) {
        this.volumeIcon.set(mdiVolumeLow);
      } else {
        this.volumeIcon.set(mdiVolumeHigh);
      }
    }
  }

  onMuteToggle() {
    let volume = this.playerService.getVolume();
    if (volume == 0) {
      this.storeAndSetVolume(0.5);
    } else {
      this.storeAndSetVolume(0);
    }

    this.setVolumeIcon();
    this.volumeInput.nativeElement.value = String(this.playerService.getVolume());
  }

  public toggleMenu() {
    this.showVolumeMenu.set(!this.showVolumeMenu());
  }

  private storeAndSetVolume(volume: number) {
    this.playerService.setVolume(volume);
    localStorage.setItem('volume', volume.toString());
  }

  ngAfterViewInit(): void {
    this.playerService.stateChanged.subscribe((_v) => this.setVolumeIcon());
    this.setInitialVolume();
  }
}

