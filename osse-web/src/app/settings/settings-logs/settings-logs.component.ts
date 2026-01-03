import { Component, OnInit, signal, WritableSignal } from '@angular/core';
import { HeaderComponent } from '../../shared/ui/header/header.component';
import { fetcher } from '../../shared/util/fetcher';
import { ToastService } from '../../toast-container/toast.service';

@Component({
  selector: 'app-settings-logs',
  imports: [HeaderComponent],
  templateUrl: './settings-logs.component.html',
  styles: ``
})
export class SettingsLogsComponent implements OnInit {
  public showLogs: WritableSignal<boolean> = signal(false);
  public logs: WritableSignal<string> = signal('');

  constructor(private notificationService: ToastService) { }

  viewLogs() {
    this.showLogs.set(true);
  }

  private async requestLogs() {
    let res = await fetcher('config/logs');

    if (res.ok) {
      this.logs.set(await res.text());
      return true;
    }

    return false;
  }

  public async refreshLogs() {
    let success = await this.requestLogs();
    if (success) {
      this.notificationService.info('Logs updated.');
    } else {
      this.notificationService.error('Failed to access logs.');
    }

  }

  ngOnInit(): void {
    this.requestLogs().then((r) => !r ? this.notificationService.error('Failed to access logs.') : null);
  }
}
