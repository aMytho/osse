import { Component, computed, OnDestroy, OnInit, signal } from '@angular/core';
import { HeaderComponent } from '../../shared/ui/header/header.component';
import { ScanChannels, ScanDirectory, ScanDirectoryStatus } from '../../shared/services/echo/channels/scan';
import { merge, Subscription } from 'rxjs';
import { EchoService } from '../../shared/services/echo/echo.service';
import { ToastService } from '../../toast-container/toast.service';
import { fetcher } from '../../shared/util/fetcher';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-settings-scan',
  imports: [HeaderComponent, CommonModule, FormsModule],
  templateUrl: './settings-scan.component.html',
  styles: ``
})
export class SettingsScanComponent implements OnInit, OnDestroy {
  public fetchingScanStatus = signal(true);

  public rootDirectories = signal<string[]>([]);
  public waitingForScanConfirmation = signal(false);

  public scanInProgress = signal(false);
  public scanProgress = signal<ScanDirectory[]>([]);

  public waitingForCancelConfirmation = signal(false);

  public scanErrorMessages = signal("");
  public scanCompleted = signal(false);
  public scanLogs = signal('');

  public freshScan = false;
  /**
   * Counts how many directories are in a complete state (scanned/errored)
   */
  public amountOfDirectoriesComplete = computed(() => {
    return this.scanProgress().filter((d) => d.status == ScanDirectoryStatus.Scanned || d.status == ScanDirectoryStatus.Errored).length;
  });
  /**
   * Percent of how many directories are in a complete state (scanned/errored)
   */
  public percentOfScanComplete = computed(() => {
    let dirs = this.scanProgress();
    // Ensure there's at least 1 directory to avoid division by zero
    if (dirs.length === 0) return 0;

    const completeDirs = dirs.filter((d) => d.status == ScanDirectoryStatus.Scanned || d.status == ScanDirectoryStatus.Errored);

    return Math.floor((completeDirs.length / dirs.length) * 100);
  });
  /**
   * Counts the total amount of directories to scan.
   */
  public totalAmountOfDirectoriesToScan = computed(() => this.scanProgress().length);

  constructor(private echoService: EchoService, private notificationService: ToastService) { }

  /**
  * This subscription links the child subscriptions and allows unsubscribing from them all at once on deInit.
  */
  private subscription!: Subscription;

  public async requestScan() {
    this.waitingForScanConfirmation.set(true);

    let scanURL = this.freshScan ? 'scan/fresh' : 'scan';

    let req = await fetcher(scanURL, {
      method: 'POST'
    });

    if (!req.ok) {
      this.notificationService.error('Failed to start scan. Please check that all directories exist, are readable, free of typos, and mounted (if a network/removeable disk)');
      this.waitingForScanConfirmation.set(false);
    }
  }

  public async cancelScan() {
    this.waitingForCancelConfirmation.set(true);

    fetcher('scan/cancel', {
      method: 'POST'
    });
  }

  public dirIsScanning(status: ScanDirectoryStatus) {
    return status == ScanDirectoryStatus.Scanning;
  }

  public dirScanned(status: ScanDirectoryStatus) {
    return status == ScanDirectoryStatus.Scanned || status == ScanDirectoryStatus.Errored;
  }

  public dirScannedOrScanning(status: ScanDirectoryStatus) {
    return this.dirScanned(status) || status == ScanDirectoryStatus.Scanning;
  }

  public getScanColor(status: ScanDirectoryStatus) {
    switch (status) {
      case ScanDirectoryStatus.Scanning:
        return "aqua";
      case ScanDirectoryStatus.Scanned:
        return "green";
      case ScanDirectoryStatus.Errored:
        return "red";
      case ScanDirectoryStatus.Pending:
        return "white";
    }
  }


  private async requestScanProgress() {
    let req = await fetcher('scan');
    if (req.ok) {
      // Set the directories.
      let resp = await req.json();
      this.rootDirectories.set(resp.rootDirectories);

      if (resp.active) {
        this.scanInProgress.set(true);
        this.scanProgress.set(resp.directories);

        let messages = [];
        // Generate the scan log.
        for (const dir of resp.directories) {
          if (dir.status != 'scanned') {
            messages.push(`${dir.path} has a status of ${dir.status}`);
          } else {
            messages.push(`Scanned ${dir.files_scanned} files and skipped ${dir.files_skipped} in ${dir.path} - Status ${dir.status}`);
          }
        }

        this.scanLogs.set(messages.join('\n'));
      }

      // If a scan is active, set that status.
    } else {
      this.notificationService.error('Something went wrong when requesting the scan status. Try reloading the page.')
    }

    this.fetchingScanStatus.set(false);
  }

  async ngOnInit(): Promise<void> {
    // Request scan progress, but don't wait for it.
    this.requestScanProgress();

    const scanStarted$ = this.echoService.subscribeToEvent(ScanChannels.ScanStarted, (data) => {
      this.notificationService.info(`Started scanning ${data.directories.length} directories.`);
      this.waitingForScanConfirmation.set(false);
      this.scanProgress.set(data.directories);
      this.scanInProgress.set(true);
      this.scanCompleted.set(false);
      this.scanLogs.set('Scan Started...');
    });
    const scanProgressed$ = this.echoService.subscribeToEvent(ScanChannels.ScanProgressed, (data) => {
      this.scanProgress.update((scanProgress) => {
        // On progress update, set the new progress. There should always be a match
        let indexOfProgressDir = -1;
        return scanProgress.map((dir, index) => {
          if (dir.id == data.directoryID) {
            dir.status = data.status;
            dir.filesScanned = data.filesScanned;
            dir.filesSkipped = data.filesSkipped;
            indexOfProgressDir = index;
            return dir;
          }

          // If its not the match, check if its the item after the match.
          // If so, set the scanning status.
          if (indexOfProgressDir != -1 && indexOfProgressDir + 1 == index) {
            dir.status = ScanDirectoryStatus.Scanning;
          }

          return dir;
        });
      })

      this.scanLogs.update((l) => l + `\nScanned dir ${data.directoryName} with ${data.filesScanned} files scanned and ${data.filesSkipped} files skipped.`);
    });
    const scanCompleted$ = this.echoService.subscribeToEvent(ScanChannels.ScanCompleted, (data) => {
      this.notificationService.info(`Finished scanning ${data.directoryCount} directories.`)
      this.scanInProgress.set(false);
      this.scanProgress.set([]);
      this.scanCompleted.set(true);
      this.scanLogs.update((l) => l + '\nScan Complete...');
    });

    const scanError$ = this.echoService.subscribeToEvent(ScanChannels.ScanError, (data) => {
      this.notificationService.error('A scan error has occured. Continuing...');
      this.scanErrorMessages.update((e) => e + data.message + '\n');
    });
    const scanFailed$ = this.echoService.subscribeToEvent(ScanChannels.ScanFailed, (data) => {
      this.notificationService.error('Scan Failed! The scan will be cancelled at the current directory.');
      this.scanErrorMessages.update((e) => e + data.message + '\n');
      this.scanInProgress.set(false);
      this.scanLogs.update((l) => l + '\nScan Failed...');
    });
    const scanCancelled$ = this.echoService.subscribeToEvent(ScanChannels.ScanCancelled, (data) => {
      this.notificationService.info(`Scan has been cancelled. ${data.directoriesScannedBeforeCancellation} directories were scanned in.`);
      this.scanInProgress.set(false);
      this.waitingForCancelConfirmation.set(false);
      this.scanProgress.set([]);
      this.scanLogs.update((l) => l + '\nScan Cancelled...');
    });

    this.subscription = merge(scanStarted$, scanProgressed$, scanCompleted$, scanError$, scanFailed$, scanCancelled$).subscribe();
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
