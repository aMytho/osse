import { EventEmitter, Injectable } from '@angular/core';
import { fetcher } from '../../util/fetcher';
import { ScanChannels, ScanEvents } from './channels/scan';
import { EchoChannel, EchoResult } from './channels';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EchoService implements ScanEvents {
  private echoEvent = new EventEmitter<{ channel: EchoChannel; data: EchoResult<EchoChannel> }>();
  private eventSource!: EventSource;

  constructor() { }

  public connect() {
    return new Promise((resolve, reject) => {
      fetcher('sse', {
        method: 'POST'
      }).then(async (r) => {
        if (r.ok) {
          let json = await r.json();

          // TODO: Use cookies instead of a query string to pass params.
          this.eventSource = new EventSource(json.url + '?id=' + json.userID + '&token=' + json.token);
          this.eventSource.addEventListener("error", (e) => console.log(e));
          // NOTE: To add a new event, you must suscribe by it to name. You can't use the generic "message" event.
          resolve(null);
        }

      }).catch(() => reject(null));
    })

  }


  public subscribeToEvent<T extends EchoChannel>(
    channel: T,
    callback?: (data: EchoResult<T>) => void
  ): Observable<EchoResult<T>> {
    return new Observable<EchoResult<T>>((observer) => {
      const subscription = this.echoEvent.subscribe(({ channel: emittedChannel, data }) => {
        if (emittedChannel === channel) {
          const eventData = data as EchoResult<T>;
          observer.next(eventData); // Emit the data through the observable
          if (callback) {
            callback(eventData); // Optionally execute the callback
          }
        }
      });

      // Clean up when the observable is unsubscribed
      return () => subscription.unsubscribe();
    });
  }

  private emitEvent<T extends EchoChannel>(channel: T, data: EchoResult<T>): void {
    console.log(channel, data);
    this.echoEvent.emit({ channel, data });
  }

  listenForScanStarted(): void {
    this.eventSource.addEventListener("ScanStarted", (ev) => this.emitEvent(ScanChannels.ScanStarted, JSON.parse(ev.data)))
  }

  listenForScanProgressed(): void {
    this.eventSource.addEventListener("ScanProgressed", (ev) => this.emitEvent(ScanChannels.ScanProgressed, JSON.parse(ev.data)))
  }

  listenForScanCompleted(): void {
    this.eventSource.addEventListener("ScanCompleted", (ev) => this.emitEvent(ScanChannels.ScanCompleted, JSON.parse(ev.data)))
  }

  listenForScanError(): void {
    this.eventSource.addEventListener("ScanError", (ev) => this.emitEvent(ScanChannels.ScanError, JSON.parse(ev.data)))
  }

  listenForScanFailed(): void {
    this.eventSource.addEventListener("ScanFailed", (ev) => this.emitEvent(ScanChannels.ScanFailed, JSON.parse(ev.data)))
  }

  listenForScanCancelled(): void {
    this.eventSource.addEventListener("ScanCancelled", (ev) => this.emitEvent(ScanChannels.ScanCancelled, JSON.parse(ev.data)))
  }

  public disconnect() {
    this.eventSource.close();
  }
}
