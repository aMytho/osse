import { ChangeDetectionStrategy, Component, ElementRef, OnInit, ViewChild, WritableSignal, signal } from '@angular/core';
import { HeaderComponent } from '../shared/ui/header/header.component';
import { ToastService } from '../toast-container/toast.service';
import { fetcher } from '../shared/util/fetcher';
import { CommonModule } from '@angular/common';
import { SettingsLogsComponent } from './settings-logs/settings-logs.component';
import { SettingsScanComponent } from "./settings-scan/settings-scan.component";
import { SettingsScanHistoryComponent } from './settings-scan-history/settings-scan-history.component';
import { SettingsPreferencesComponent } from "./settings-preferences/settings-preferences.component";
import { SettingsAccountComponent } from './settings-account/settings-account.component';

@Component({
  selector: 'app-settings',
  imports: [HeaderComponent, CommonModule, SettingsLogsComponent, SettingsScanComponent, SettingsScanHistoryComponent, SettingsPreferencesComponent, SettingsAccountComponent],
  templateUrl: './settings.component.html',
  styles: ``,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SettingsComponent implements OnInit {
  @ViewChild('samples') sampleElement!: ElementRef<HTMLInputElement>;
  public activeTab = signal('scan');

  public directories: WritableSignal<string[]> = signal([]);

  constructor(
    private notificationService: ToastService,
  ) { }

  public async requestSettings() {
    let res = await fetcher('config');

    if (res.ok) {
      let response = await res.json();
      this.directories.set(response.directories);
    } else {
      this.notificationService.error('Failed to reach server. Check that the URL is correct and that the server is running.');
    }
  }

  async ngOnInit(): Promise<void> {
    await this.requestSettings();
  }
}
