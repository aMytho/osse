import { Injectable } from '@angular/core';
import { fetcher } from '../../util/fetcher';
import { Track } from './track';
import { OsseTrack } from './osse-track';
import { ConfigService } from '../config/config.service';

@Injectable({
  providedIn: 'root'
})
export class QueueSyncService {
  private queueDebounce: number = 0;

  private lastSyncedIndex: number | null = null;
  private lastSyncedPosition: number = 0;
  private currentIndex: number | null = null;
  private currentPosition: number = 0;

  constructor(private configService: ConfigService) {
    setInterval(() => {
      if (
        this.currentIndex !== this.lastSyncedIndex ||
        this.currentPosition !== this.lastSyncedPosition
      ) {
        this.lastSyncedIndex = this.currentIndex;
        this.lastSyncedPosition = this.currentPosition;

        if (this.configService.get('queue')) {
          fetcher('queue/active-track', {
            method: 'POST',
            body: JSON.stringify({
              active_track_index: this.currentIndex,
              track_position: this.currentPosition
            })
          });
        }

      }
    }, 30000);
  }

  /**
  * Syncs the queue in 5 seconds, with debounce.
  */
  syncQueue(trackIds: number[], trackIndex: number | null) {
    if (this.configService.get('queue')) {
      clearTimeout(this.queueDebounce);
      this.queueDebounce = setTimeout(() => {
        fetcher('queue', {
          method: 'POST',
          body: JSON.stringify({
            'ids': trackIds,
            // The active track is the input of the user if the track length is > 1, otherwise its null.
            // Null symbolizes unknown curent track, or no tracks to choose from.
            'active_track': trackIds.length > 0 ? (trackIndex ?? 0) : null,
            'track_position': this.currentPosition,
          })
        })
      }, 5000);
    }
  }

  /**
  * Sets the active track and position server side.
  * Syncs on an interval of 20 seconds.
  */
  syncActiveTrack(trackIndex: number | null, trackPosition: number = 0) {
    this.currentIndex = trackIndex;
    this.currentPosition = Math.floor(trackPosition);
  }

  async getQueueFromServer(): Promise<TrackQueue> {
    if (!this.configService.get('queue')) {
      return {
        queue: [],
        trackIndex: null,
        trackPosition: 0
      };
    }

    let req = await fetcher('queue');
    if (req.ok) {
      let result: QueueResponse = await req.json();
      if (result.trackIndex != null) {
        return {
          queue: result.tracks.map((t) => new Track(t)),
          trackIndex: result.trackIndex,
          trackPosition: result.trackPosition
        }
      }
    }

    return {
      queue: [],
      trackIndex: null,
      trackPosition: 0
    };
  }
}

type QueueResponse = {
  tracks: OsseTrack[];
  // Index of current track.
  trackIndex: number | null;
  // Seconds into current track.
  trackPosition: number;
}

type TrackQueue = {
  queue: Track[];
  trackIndex: number | null;
  trackPosition: number;
}
