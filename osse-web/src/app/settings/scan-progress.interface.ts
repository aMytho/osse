export interface ScanProgress {
  active: boolean;
  // If active is true, the below fields are present.
  total_directories?: number;
  finished_count?: number
  nextDir?: string | null;
}
