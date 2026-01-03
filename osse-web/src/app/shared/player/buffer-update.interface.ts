export interface BufferUpdate {
  /**
   * Duration from the audio player.
   * This is usually inaccurate at the early parts of the song when it is not entirely downloaded.
   * It is usually accurate at the end when the entire song is downloaded.
  */
  duration: number;
  buffered: TimeRanges;
}
