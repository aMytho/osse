import { Component, ElementRef, ViewChild, OnInit, OnDestroy } from '@angular/core';
import { WebAudioService } from '../web-audio.service';

@Component({
  selector: 'app-visualizer',
  templateUrl: './visualizer.component.html',
})
export class VisualizerComponent implements OnInit, OnDestroy {
  @ViewChild('canvas', { static: true }) canvas!: ElementRef<HTMLCanvasElement>;
  private ctx!: CanvasRenderingContext2D;
  private animationFrameId!: number;
  private width = 0;
  private height = 0;
  private resizeObserver!: () => void;
  private resizeTimeout = 0;

  constructor(private webAudioService: WebAudioService) { }

  ngOnInit() {
    this.ctx = this.canvas.nativeElement.getContext('2d')!;

    // Listen for resize events to rescale canvas
    this.resizeObserver = () => {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = setTimeout(() => this.resizeCanvas(), 300);
    }
    window.addEventListener('resize', this.resizeObserver);
    window.addEventListener('fullscreenchange', () => this.resizeCanvas());

    this.resizeCanvas();
    this.drawVisualizer();
  }

  private resizeCanvas() {
    const canvas = this.canvas.nativeElement;
    const dpr = window.devicePixelRatio || 1;

    // Set canvas size based on client size and DPR
    canvas.width = canvas.clientWidth * dpr;
    canvas.height = canvas.clientHeight * dpr;
    // Store local copies for easier access in drawing functions
    this.width = canvas.width * dpr;
    this.height = canvas.height * dpr;

    // Scale context so drawing operations match the high resolution
    this.ctx.resetTransform(); // Reset to avoid cumulative scaling
    this.ctx.scale(dpr, dpr);
  }

  private drawVisualizer() {
    this.ctx.clearRect(0, 0, this.width, this.height);

    const draw = () => {
      this.animationFrameId = requestAnimationFrame(draw);
      this.ctx.clearRect(0, 0, this.width, this.height);

      this.drawFrequencyBars();
    };
    draw();
  }

  private drawFrequencyBars() {
    const data = this.webAudioService.getFrequencyData();
    const barWidth = this.canvas.nativeElement.width / data.length;

    data.forEach((value, i) => {
      const barHeight = (value / 255) * this.canvas.nativeElement.height;

      // Create gradient from bottom (green) to top (light green)
      const gradient = this.ctx.createLinearGradient(0, this.canvas.nativeElement.height - barHeight, 0, this.canvas.nativeElement.height);
      gradient.addColorStop(0, 'rgb(52, 211, 153)');  // Bottom color
      gradient.addColorStop(1, 'rgb(167, 243, 208)'); // Top color

      this.ctx.fillStyle = gradient;
      this.ctx.fillRect(i * barWidth, this.canvas.nativeElement.height - barHeight, barWidth, barHeight);
    });
  }

  ngOnDestroy() {
    cancelAnimationFrame(this.animationFrameId);
    clearTimeout(this.resizeTimeout);
    window.removeEventListener('resize', this.resizeObserver);
    window.removeEventListener('fullscreenchange', () => this.resizeCanvas());
  }
}
