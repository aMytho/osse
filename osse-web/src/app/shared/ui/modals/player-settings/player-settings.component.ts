import { Component, ElementRef, EventEmitter, Input, OnInit, Output, signal, ViewChild, WritableSignal } from '@angular/core';
import { ConfigService } from '../../../services/config/config.service';
import { ToastService } from '../../../../toast-container/toast.service';

@Component({
  selector: 'app-player-settings',
  imports: [],
  templateUrl: './player-settings.component.html',
  styles: ``
})
export class PlayerSettingsComponent implements OnInit {
  @Input() visualizerSignal!: WritableSignal<boolean>;
  @Output() onClose = new EventEmitter();
  @ViewChild('samples') sampleElement!: ElementRef<HTMLInputElement>;
  public showVisualizer: WritableSignal<boolean> = signal(false);
  public visualizerSamples: WritableSignal<number> = signal(1);

  constructor(private configService: ConfigService, private notificationService: ToastService) { }

  public save() {
    this.configService.save('showVisualizer', this.showVisualizer());
    this.configService.save('visualizerSamples', Number(this.sampleElement.nativeElement.value));
    this.visualizerSamples.set(Number(this.sampleElement.nativeElement.value));
    // This will cause a visualizer change. The samples are pulled from the config service directly.
    this.visualizerSignal.set(this.showVisualizer());
    this.notificationService.info('Saved Preferences!');

    this.close();
  }

  public close() {
    this.onClose.emit();
  }

  ngOnInit(): void {
    this.showVisualizer.set(this.configService.get('showVisualizer'));
    this.visualizerSamples.set(this.configService.get('visualizerSamples'));
  }
}
