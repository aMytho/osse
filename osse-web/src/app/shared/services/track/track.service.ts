import { Injectable, signal, WritableSignal } from '@angular/core';
import { Track } from './track';
import { PlayerService } from '../../player/player.service';
import { Repeat } from './repeat.enum';
import { QueueSyncService } from './queue-sync.service';

/**
 * This service stores all queued tracks
 */
@Injectable({
  providedIn: 'root'
})
export class TrackService {
  /**
   * List of tracks in the queue. We can only call methods on it. DO NOT reset the value or the UI loses reference.
   */
  public tracks: Track[] = [];
  private index = 0;
  private hasRepeatedCurrentTrack: boolean = false;

  /**
   * List of cleared tracks. Used for restoration in case of accidental deletion.
   */
  private clearedTracks: Track[] = [];

  /**
   * If true, track is removed on end playback
  */
  public consume: WritableSignal<boolean> = signal(false);
  public repeat: WritableSignal<Repeat> = signal(Repeat.None);

  constructor(private playerService: PlayerService, private queueSyncService: QueueSyncService) {
    // When playback ends, wait 250 ms.
    // We need the player to clear the UI first. It subscribes to the same event.
    // Then, progress to the next track (if any)
    this.playerService.playbackEnded.subscribe(_ => {
      setTimeout(() => {
        if (this.tracks.length <= 0) return;

        // Handle repeats.
        let trackPlayedFromRepeat = this.playTrackBasedOnRepeatValue();
        if (trackPlayedFromRepeat) {
          return;
        }

        if (this.consume()) {
          this.tracks.splice(this.index, 1);
          if (this.tracks.length == 0) {
            this.playerService.clearTrack();
          } else {
            this.moveToLastTrack();
          }
        } else {
          this.moveToNextTrack();
        }
      }, 250);
    })

    // Keep the server in synce with playback. There is a throttle of 20 seconds.
    this.playerService.trackPositionUpdate.subscribe((pos) => {
      this.queueSyncService.syncActiveTrack(this.index, pos.currentTimeSeconds);
    });
  }

  get activeTrack() {
    return this.tracks[this.index];
  }

  public addTrack(track: Track) {
    // If the UUID is already in use, make a new uuid.
    if (this.tracks.some((a) => a.uuid == track.uuid)) {
      track = track.regenerateTrack();
    }

    this.tracks.push(track);
    // If this is the first track added, start playback
    if (this.tracks.length - 1 == 0) {
      this.moveToTrack(0);
    }

    this.queueSyncService.syncQueue(this.tracks.map((t) => t.id), this.index);
  }

  // Removes all tracks and stops playback
  public clearTracks() {
    // Store a list of the cleared tracks.
    this.clearedTracks = this.tracks.map((t) => t);

    while (this.tracks.length != 0) {
      this.tracks.pop();
    }

    this.index = 0;
    this.playerService.pause();
    this.playerService.clearTrack();

    this.queueSyncService.syncQueue([], null);
  }

  /**
   * Called when the user leaves the homepage.
   */
  public removeClearedTracks() {
    this.clearedTracks = [];
  }


  public restoreTracks() {
    if (this.clearedTracks.length == 0) {
      return;
    }

    // If we ever allow this method to be called outside of the homepage, we may need to clear any existing tracks first.
    this.clearedTracks.forEach((t) => this.addTrack(t));
    this.clearedTracks = [];

    this.queueSyncService.syncQueue(this.tracks.map((t) => t.id), null);
  }

  public moveToNextTrack() {
    this.index += 1;
    if (this.index == this.tracks.length) {
      this.index = 0;
    }

    if (this.tracks[this.index]) {
      this.playerService.setTrackAndPlay(this.activeTrack);
      // If user goes to next track, clear repeat tracker.
      this.hasRepeatedCurrentTrack = false;
    }
  }

  public moveToLastTrack() {
    this.index -= 1;
    if (this.index < 0) {
      this.index = 0;
    }

    if (this.tracks[this.index]) {
      this.playerService.setTrackAndPlay(this.activeTrack);
      // If user goes to last track, clear repeat tracker.
      this.hasRepeatedCurrentTrack = false;
    }
  }

  public moveToTrack(index: number) {
    this.index = index;

    this.playerService.setTrackAndPlay(this.activeTrack);
  }

  /**
   * Same as moveToTrack, but it won't start playback.
   */
  public setTrackIndex(index: number) {
    this.index = index;
  }

  public removeTrack(index: number) {
    // If 1 track is present, remove them and end playback
    if (this.tracks.length == 1) {
      this.tracks.pop();
      this.index = 0;

      this.playerService.pause();
      this.playerService.clearTrack();
      return;
    }

    // If the track is after the current track remove it
    if (index > this.index) {
      this.tracks.splice(index, 1);
    } else if (index == this.index) {
      // If its the current track, stop playback, remove it, and play next
      this.tracks.splice(index, 1);

      this.playerService.pause();
      this.playerService.clearTrack();

      // Reduce the index by one to play the "next" track
      this.moveToLastTrack();
    } else {
      // Track is before current, remove it and move index back 1
      this.tracks.splice(index, 1);
      this.index -= 1;
    }

    this.queueSyncService.syncQueue(this.tracks.map((t) => t.id), this.index);
  }

  /**
   * Shuffles the tracks and moves the index to the new location of the active track (if any)
   */
  public shuffle() {
    let currentTrack = this.activeTrack;

    this.tracks = this.tracks
      .map(value => ({ value, sort: Math.random() }))
      .sort((a, b) => a.sort - b.sort)
      .map(({ value }) => value);

    if (currentTrack) {
      this.index = this.tracks.findIndex((t) => t.id == currentTrack.id);
    }

    this.queueSyncService.syncQueue(this.tracks.map((t) => t.id), this.index);
  }

  /**
  * Sets the queue to whatever the user has server side.
  */
  public fetchQueueFromServer(play: boolean = false) {
    // Move duration to 2nd method.
    this.queueSyncService.getQueueFromServer()
      .then((result) => {
        result.queue.forEach(t => this.tracks.push(t));

        if (this.tracks.length == 0) return;

        // If the session has a track, use it.
        if (result.trackIndex != null) {
          // There may have been a index desync, so make sure the index is valid.
          if (!this.canMoveToTrack(result.trackIndex)) {
            if (play) {
              this.playerService.setTrackAndPlay(result.queue[0]);
            } else {
              this.playerService.setTrackAndBackgroundImage(result.queue[0]);
            }

            // Set the track index so active track is correct. (it would play the right track, but the active track is wrong which causes UI issues.)
            this.setTrackIndex(0);

            return;
          }

          if (play) {
            this.playerService.setTrackAndPlay(result.queue[result.trackIndex], result.trackPosition ?? 0);
          } else {
            this.playerService.setTrackAndBackgroundImage(result.queue[result.trackIndex]);
            this.playerService.setDuration(result.trackPosition ?? 0);
          }

          this.setTrackIndex(result.trackIndex);
        } else {
          // Else, use first track.
          if (play) {
            this.playerService.setTrackAndPlay(result.queue[0]);
          } else {
            this.playerService.setTrackAndBackgroundImage(result.queue[0]);
          }

          this.setTrackIndex(0);
        }
      });
  }

  private canMoveToTrack(index: number) {
    return index < this.tracks.length;
  }

  private playTrackBasedOnRepeatValue(): boolean {
    switch (this.repeat()) {
      case Repeat.None:
        return false;
      case Repeat.Once:
        if (this.hasRepeatedCurrentTrack) {
          // Move to the next track.
          this.moveToNextTrack();
        } else {
          this.playerService.setTrackAndPlay(this.activeTrack);
          this.hasRepeatedCurrentTrack = true;
        }
        return true;
      case Repeat.Loop:
        this.playerService.setTrackAndPlay(this.activeTrack);
        return true;
    }
  }

  public getUpcomingTrack(): Track | undefined {
    let track = this.tracks.at(this.index + 1);
    if (track != undefined) {
      return track;
    }

    // If there isn't a next track, try the first track (expecting loop around)
    return this.tracks.at(0);
  }
}
