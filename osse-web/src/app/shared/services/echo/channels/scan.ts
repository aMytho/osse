/**
 * Listen for Scan Events.
 */
export interface ScanEvents {
  listenForScanStarted(): void;
  listenForScanProgressed(): void;
  listenForScanCompleted(): void;
  listenForScanError(): void;
  listenForScanFailed(): void;
  listenForScanCancelled(): void;
}

export interface ScanStartedResult {
  directories: ScanDirectory[];
}

export interface ScanDirectory {
  id: number;
  scanJobID: number;
  path: string;
  status: ScanDirectoryStatus;
  filesScanned: number;
  filesSkipped: number;
}

export enum ScanDirectoryStatus {
  Pending = 'pending',
  Scanning = 'scanning',
  Scanned = 'scanned',
  Errored = 'errored',
}

export interface ScanProgressedResult {
  directoryID: number;
  directoryName: string;
  filesScanned: number;
  filesSkipped: number;
  status: ScanDirectoryStatus;
}

export interface ScanCompletedResult {
  directoryCount: number;
}

export interface ScanErrorResult {
  message: string;
}

export interface ScanFailedResult {
  message: string;
}

export interface ScanCancelledResult {
  directoriesScannedBeforeCancellation: number;
}

export interface ScanEventMap {
  ScanStarted: ScanStartedResult;
  ScanProgressed: ScanProgressedResult;
  ScanCompleted: ScanCompletedResult;
  ScanError: ScanErrorResult;
  ScanFailed: ScanFailedResult;
  ScanCancelled: ScanCancelledResult;
}


/**
 * Scan channel names to subscrbe to.
 */
export enum ScanChannels {
  ScanStarted = "ScanStarted",
  ScanProgressed = "ScanProgressed",
  ScanCompleted = "ScanCompleted",
  ScanError = "ScanError",
  ScanFailed = "ScanFailed",
  ScanCancelled = "ScanCancelled",
}
