import { Component, OnInit, signal } from '@angular/core';
import { HeaderComponent } from "../../shared/ui/header/header.component";
import { fetcher } from '../../shared/util/fetcher';
import { ToastService } from '../../toast-container/toast.service';
import { ScanJob } from './history';

@Component({
  selector: 'app-settings-scan-history',
  imports: [HeaderComponent],
  templateUrl: './settings-scan-history.component.html',
  styles: ``
})
export class SettingsScanHistoryComponent implements OnInit {
  public isLoading = signal(true);
  public jobs = signal<ScanJob[]>([])

  constructor(private notificationService: ToastService) { }

  public async requestHistory() {
    let req = await fetcher('scan/history');
    if (req.ok) {
      // Set the directories.
      let resp: ScanJob[] = await req.json();
      this.jobs.set(resp.map((j) => {
        let start = new Date(j.started_at);
        j.started_at = start.toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });

        if (!j.finished_at) {
          j.finished_at = 'now...'
        } else {
          let end = new Date(j.finished_at);
          j.finished_at = end.toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });
        }

        return j;
      }));

      // If a scan is active, set that status.
    } else {
      this.notificationService.error('Something went wrong when requesting the scan history. Try reloading the page.');
    }

    this.isLoading.set(false);
  }

  ngOnInit(): void {
    this.requestHistory();
  }
}
