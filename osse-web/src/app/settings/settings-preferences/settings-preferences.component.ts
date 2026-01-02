import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ConfigService } from '../../shared/services/config/config.service';
import { HeaderComponent } from "../../shared/ui/header/header.component";
import { IconComponent } from "../../shared/ui/icon/icon.component";
import { mdiChartBar, mdiContentSave, mdiImage, mdiRestore } from '@mdi/js';
import { fetcher } from '../../shared/util/fetcher';
import { ToastService } from '../../toast-container/toast.service';
import { OsseConfigResponse } from './osse-config';

@Component({
  selector: 'app-settings-preferences',
  imports: [ReactiveFormsModule, HeaderComponent, IconComponent],
  templateUrl: './settings-preferences.component.html',
  styles: ``
})
export class SettingsPreferencesComponent implements OnInit {
  private formBuilder = inject(FormBuilder);
  preferencesForm = this.formBuilder.group({
    showBackgroundArt: [false],
    showVisualizer: [false],
    visualizerSamples: [1, [Validators.min(1), Validators.max(10)]],
    enableQueue: [false],
  });

  public waitingForResponse = signal(false);

  public visualizerIcon = mdiChartBar;
  public imageIcon = mdiImage;
  public queueIcon = mdiRestore;
  public saveIcon = mdiContentSave;

  constructor(private configService: ConfigService, private notificationService: ToastService) { }

  async onSubmit() {
    // Only save valid data
    if (!this.preferencesForm.valid) {
      return;
    }

    this.waitingForResponse.set(true);

    // Save local data first
    this.configService.saveMany({
      showCoverBackgrounds: this.preferencesForm.value.showBackgroundArt as boolean,
      showVisualizer: this.preferencesForm.value.showVisualizer as boolean,
      visualizerSamples: this.preferencesForm.value.visualizerSamples as number,
    });

    // Now, save account data
    let res = await fetcher('config', {
      method: 'POST',
      body: JSON.stringify({
        enableQueue: this.preferencesForm.value.enableQueue ?? false
      })
    });

    if (res.ok) {
      this.notificationService.info('Preferences Saved!');
    } else {
      this.notificationService.error('Failed to save account preferences. Try saving again.');
    }

    this.waitingForResponse.set(false);
  }

  private async requestSettings(): Promise<OsseConfigResponse> {
    let res = await fetcher('config');

    if (res.ok) {
      let response = await res.json();
      return response as OsseConfigResponse;
    } else {
      this.notificationService.error('Failed to reach server. Check that the URL is correct and that the server is running.');
      throw 'Config Error';
    }
  }

  async ngOnInit(): Promise<void> {
    this.waitingForResponse.set(true);

    let conf;
    try {
      conf = await this.requestSettings();
    } catch (error) { }

    // Store the values and set them in the form
    this.preferencesForm.setValue({
      showBackgroundArt: this.configService.get('showCoverBackgrounds'),
      showVisualizer: this.configService.get('showVisualizer'),
      visualizerSamples: this.configService.get('visualizerSamples'),
      enableQueue: conf?.queueEnabled ?? false,
    });

    this.waitingForResponse.set(false);
  }
}
