import { AfterViewInit, Component, ElementRef, ViewChild } from '@angular/core';
import { WebAudioService } from '../web-audio.service';
import { mdiRestart } from '@mdi/js';
import { IconComponent } from '../../ui/icon/icon.component';

@Component({
  selector: 'app-pan-controls',
  imports: [IconComponent],
  templateUrl: './pan-controls.component.html',
  styles: ``
})
export class PanControlsComponent implements AfterViewInit {
  @ViewChild('pan') panInput!: ElementRef<HTMLInputElement>;
  reset = mdiRestart;

  constructor(private webAudioService: WebAudioService) { }

  setInitialPan(): void {
    this.storeAndSetPan(Number(localStorage.getItem('pan') ?? 0));
    this.panInput.nativeElement.value = String(this.webAudioService.getPanValue());
  }

  onPanChange(event: any) {
    this.storeAndSetPan(event.target.value);
  }

  adjustPanByScroll(event: any) {
    event.preventDefault();
    let currentPan = this.webAudioService.getPanValue();

    let newPan;
    if (event.deltaY > 0) {
      newPan = Math.max(-1, currentPan - 0.05);
    } else {
      newPan = Math.min(1, currentPan + 0.05);
    }

    this.panInput.nativeElement.value = String(newPan);
    this.storeAndSetPan(newPan);
  }

  onPanReset() {
    this.panInput.nativeElement.value = "0";
    // The event isn't triggered.
    this.storeAndSetPan(0);
  }

  private storeAndSetPan(pan: number) {
    this.webAudioService.setPan(pan);
    localStorage.setItem('pan', pan.toString());
  }

  ngAfterViewInit(): void {
    this.setInitialPan();
  }
}

