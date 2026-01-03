/**
 * Available fields to show in a track matrix UI.
*/
export enum TrackField {
  TrackNumber,
  Title,
  Artist,
  Duration
}

/**
 * Track fields wrapper.
*/
export class TrackInfo {
  public static allFields() {
    return [TrackField.TrackNumber, TrackField.Title, TrackField.Artist, TrackField.Duration];
  }

  public static default() {
    return [TrackField.Title, TrackField.Artist, TrackField.Duration];
  }
}
