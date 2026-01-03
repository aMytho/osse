import { Component, OnInit, signal } from '@angular/core';
import { LoadingService } from './loading.service';

@Component({
  selector: 'app-loading',
  imports: [],
  templateUrl: './loading.component.html',
  styleUrl: `./loading.component.css`,
})
export class LoadingComponent implements OnInit {
  // Front loading bar
  isLoading = signal(false);
  opacity = signal(1);
  componentReady = signal(false);

  // Back loading bar.
  isFastEndToLoading = signal(false);

  constructor(private loadingService: LoadingService) { }

  /**
  * Resets animations.
  * Automatically called by the bottom bar if fastEndToLoading is set.
  * Otherwise called at end of load.
  */
  resetAnimations() {
    this.opacity.set(0);

    setTimeout(() => {
      this.isFastEndToLoading.set(false);
      this.isLoading.set(false);
      this.opacity.set(1);
    }, 1000);
  }

  ngOnInit(): void {
    this.loadingService.loadingStarted.subscribe(() => {
      // Show the loading bars. Prevents flicker.
      // TODO: Find a better way than checking it each time. NgOnInit and NgAfterViewInit didn't work.
      if (!this.componentReady()) {
        this.componentReady.set(true);
      }

      // Start loading animation.
      this.isLoading.set(true);
    });

    this.loadingService.loadingFinished.subscribe((useFastEndAnimation) => {
      if (useFastEndAnimation) {
        // Show the bottom bar to appear to end the animation faster.
        // The bottom bar will call the reset when its done.
        this.isFastEndToLoading.set(true);
      } else {
        // End the enimation.
        this.resetAnimations();
      }
    });
  }
}
