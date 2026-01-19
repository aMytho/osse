/**
 * A successful auth response.
 */
export interface AuthResponse {
  /**
   * User ID
   */
  id: number,
  username: string,
  settings: UserSettings;
}

export interface UserSettings {
  id: number;
  queue: boolean;
}
