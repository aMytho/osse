import { Track } from "../services/track/track";
import { getNicelyFormattedTime } from "../util/time";

export class TrackUpdate {
  constructor(private track: Track, private info: TrackPlayerInfo) { }

  get totalSeconds() {
    return this.track.duration;
  }

  get currentSecond() {
    return this.info.time;
  }

  get title() {
    return this.track.title;
  }

  get artist() {
    if (this.track.hasArtist()) {
      return this.track.artistPrimary();
    }

    return null;
  }

  get durationFormatted() {
    // Sometimes the audio player duration estimate is undetectable. Return the metadata duration in that case.
    if (isNaN(this.info.totalDurationEstimate)) {
      return getNicelyFormattedTime(this.track.duration + 1);
    } else {
      // return the metadata duration or the audio duration, whichever is bigger.
      return getNicelyFormattedTime(Math.max(this.track.duration + 1, this.info.totalDurationEstimate));
    }
  }

  get timeFormatted() {
    return getNicelyFormattedTime(this.info.time);
  }

  get id() {
    return this.track.track.id;
  }

  get cover() {
    return this.track.coverURL;
  }
}

export interface TrackPlayerInfo {
  time: number;
  totalDurationEstimate: number;
}
