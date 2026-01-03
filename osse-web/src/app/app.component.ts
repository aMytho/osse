import { Component, ElementRef, Injector, signal, ViewChild, WritableSignal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavigationComponent } from './navigation/navigation.component';
import { PlayerComponent } from './shared/player/player.component';
import { ToastContainerComponent } from './toast-container/toast-container.component';
import { BackgroundImageService } from './shared/ui/background-image.service';
import { PlayerService } from './shared/player/player.service';
import { PlaybackState } from './shared/player/state-change';
import { ModalComponent } from './shared/ui/modal/modal.component';
import { LocatorService } from './locator.service';
import { AuthService } from './shared/services/auth/auth.service';
import { CommonModule } from '@angular/common';
import { LoadingComponent } from './shared/ui/loading/loading.component';
import { NetworkService } from './shared/services/network/network.service';
import { PreloadService } from './shared/player/preload/preload.service';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, NavigationComponent, PlayerComponent, ToastContainerComponent, ModalComponent, LoadingComponent, CommonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'Osse';
  playerState: WritableSignal<boolean> = signal(false);
  showPlayer: WritableSignal<boolean> = signal(false);
  @ViewChild('outlet') outlet!: ElementRef;

  constructor(
    private backgroundImageService: BackgroundImageService,
    private playerService: PlayerService,
    private injector: Injector,
    private networkService: NetworkService,
    private authService: AuthService,
    private preloadService: PreloadService
  ) {
    // Allow shared injector for accessing services
    LocatorService.injector = this.injector;

    this.backgroundImageService.bgChanged.subscribe((v) => {
      this.outlet.nativeElement.style.setProperty('--bg', `url('${v}')`);
    });
    this.playerService.stateChanged.subscribe((s) => {
      this.playerState.set(s == PlaybackState.Playing);
    });
    this.authService.authStateChanged.subscribe((v) => this.showPlayer.set(v));
  }
}

