export interface ScanJob {
  id: number;
  finished_at: string;
  started_at: string;
  status: string;
  total_dirs: number;
  directories: ScanDirectory[];
}

export interface ScanDirectory {
  id: number;
  files_scanned: number;
  files_skipped: number;
  status: string;
  path: string;
  errors: ScanError[];
  show: boolean; // This doesn't exist, but we add it client side.
}

export interface ScanError {
  id: number;
  scan_directory_id: number;
  error: string;
  created_at: string;
}
