import { AfterViewInit, Component, ElementRef, signal, ViewChild, WritableSignal } from '@angular/core';
import { PlayerService } from './player.service';
import { PointState } from './point-state';
import { RouterLink } from '@angular/router';
import { IconComponent } from '../ui/icon/icon.component';
import { mdiDotsVertical } from '@mdi/js';
import { BufferUpdate } from './buffer-update.interface';
import { getNicelyFormattedTime } from '../util/time';
import { MediaSessionService } from './media-session.service';
import { PopoverControlsComponent } from './popover-controls/popover-controls.component';
import { CommonModule } from '@angular/common';
import { TrackControlsComponent } from './track-controls/track-controls.component';
import { PlayPauseComponent } from './track-controls/play-pause/play-pause.component';

@Component({
  selector: 'app-player',
  imports: [PopoverControlsComponent, TrackControlsComponent, PlayPauseComponent, IconComponent, RouterLink, CommonModule],
  templateUrl: './player.component.html',
  styleUrl: `./player.component.css`
})
export class PlayerComponent implements AfterViewInit {
  @ViewChild('progressContainer') container!: ElementRef<HTMLDivElement>;
  @ViewChild('point') point!: ElementRef<HTMLDivElement>;
  @ViewChild('rendered') rendered!: ElementRef<HTMLDivElement>;
  @ViewChild('trackTitleElement') trackTitleElement!: ElementRef<HTMLParagraphElement>;
  @ViewChild('popoverControls') popoverControls!: ElementRef<HTMLDialogElement>;

  public bg: WritableSignal<string> = signal("assets/img/osse.webp");
  public currentTime: WritableSignal<string> = signal('');
  public totalDuration: WritableSignal<string> = signal('');
  public trackTitle: WritableSignal<string> = signal('');
  public artistTitle: WritableSignal<string> = signal('');
  private isDragging = false;
  private abortMouseMove = new AbortController();
  private seekDuration = 0;
  private resizeTimer = 0;

  verticalDots = mdiDotsVertical;

  constructor(
    public playerService: PlayerService,
    private mediaSessionService: MediaSessionService
  ) {
    // Make sure the mouse up is accessible in global contexts
    this.onMouseUp = this.onMouseUp.bind(this);
    this.onMouseMove = this.onMouseMove.bind(this);

    // Listen for the resize event
    window.addEventListener('resize', () => {
      this.queueResizeCheck();
    })
  }

  private queueResizeCheck() {
    clearTimeout(this.resizeTimer);
    this.resizeTimer = setTimeout(() => {
      this.setTitleAnimationByScreenSize();
    }, 150);
  }

  onBackdropClick(ev: any) {
    if (ev.target.id == 'popover') {
      this.popoverControls.nativeElement.close();
    }
  }

  onMouseDown(ev: MouseEvent) {
    this.isDragging = true;
    ev.preventDefault();

    this.setPointState(PointState.Pause);

    document.addEventListener('mousemove', this.onMouseMove, { signal: this.abortMouseMove.signal });
    document.addEventListener('mouseup', this.onMouseUp, { once: true });
  }

  onMouseMove(ev: MouseEvent) {
    if (this.isDragging) {
      const progressBarRect = this.container.nativeElement.getBoundingClientRect();
      const progressBarWidth = progressBarRect.width;
      const newPositionX = ev.clientX - progressBarRect.left;

      // Ensure the new position is within the bounds of the progress bar
      const clampedPositionX = Math.max(0, Math.min(progressBarWidth, newPositionX));

      // Update the position of the progress point
      this.point.nativeElement.style.left = clampedPositionX + 'px';
      this.seekDuration = (clampedPositionX / progressBarWidth);
    }
  }

  onMouseUp(_ev: any) {
    this.abortMouseMove.abort();
    this.abortMouseMove = new AbortController();
    this.setPointState(PointState.Play);
    this.playerService.play(this.seekDuration * this.playerService.duration());
    this.isDragging = false;
  }

  onSetPosition(ev: MouseEvent) {
    this.onMouseDown(ev);
    this.onMouseMove(ev);
  }

  setPointState(state: PointState) {
    switch (state) {
      case PointState.Pause:
        this.point.nativeElement.classList.add('paused');
        this.point.nativeElement.classList.remove('playing');
        break;
      case PointState.Play:
        this.point.nativeElement.classList.add('playing');
        this.point.nativeElement.classList.remove('paused');
        break;
      default:
        break;
    }
  }

  setGradient(start: number, color: string, end?: number) {
    if (end == undefined) {
      this.rendered.nativeElement.style.setProperty('--bar-c-' + start, color);
    } else {
      for (let i = start; i < end; i++) {
        this.rendered.nativeElement.style.setProperty('--bar-c-' + i, color);
      }
    }
  }

  private onBufferProgress(bufferUpdate: BufferUpdate) {
    const { duration, buffered } = bufferUpdate;

    this.point.nativeElement.style.animationDuration = duration + "s";

    if (duration > 0) {
      for (let i = 0; i < buffered.length; i++) {
        let start = buffered.start(i) / duration * 100;
        let end = buffered.end(i) / duration * 100;
        this.setGradient(Math.floor(start), "var(--point-buffered)", Math.floor(end));
      }
    }
  }

  private setTitleAnimationByScreenSize() {
    if (this.trackTitleElement.nativeElement.offsetWidth < this.trackTitleElement.nativeElement.parentElement!.offsetWidth) {
      // Pause, set text to be visible
      this.trackTitleElement.nativeElement.style.animationPlayState = 'paused';
      let anim = this.trackTitleElement.nativeElement.getAnimations()[0];
      let duration = anim.effect!.getTiming().duration;
      anim.currentTime = (duration as number) / 2;
    } else {
      this.trackTitleElement.nativeElement.style.animationPlayState = 'running';
    }
  }

  /**
   * When the audio player is fully loaded, send the audio element to player service
   */
  ngAfterViewInit(): void {
    this.playerService.trackUpdated.subscribe((val) => {
      this.totalDuration.set(val.durationFormatted);
      this.trackTitle.set(val.title);
      this.artistTitle.set(val.artist?.name ?? '');
      // Set the cover bg
      this.bg.set(val.cover);
      this.setTitleAnimationByScreenSize();
      this.setGradient(0, "transparent", 100);
      this.queueResizeCheck();
    });

    this.playerService.trackPositionUpdate.subscribe((val) => {
      this.currentTime.set(getNicelyFormattedTime(val.currentTimeSeconds))

      // If the user is not seeking, update the position
      if (!this.isDragging) {
        this.point.nativeElement.style.left = Math.floor((val.currentTimeSeconds / val.totalTimeSeconds) * 100) + "%";
      }

      // Set the duration as we may have a more accurate total duration.
      this.totalDuration.set(getNicelyFormattedTime(val.totalTimeSeconds));
    });

    this.playerService.playbackEnded.subscribe(_ => {
      this.totalDuration.set('');
      this.currentTime.set('');
      this.trackTitle.set('');
      this.artistTitle.set('');
      this.bg.set('assets/img/osse.webp');
      this.setGradient(0, "transparent", 100);
    });

    this.playerService.bufferUpdated.subscribe((ev) => this.onBufferProgress(ev));
  }
}
