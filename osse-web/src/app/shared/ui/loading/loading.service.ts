import { EventEmitter, Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class LoadingService {

  constructor() { }
  public loadingStarted = new EventEmitter();
  /**
  * Emits when loading is done.
  * If the loading animation hasn't finished, we emit true to tell the background bar to appear (appear to end animation faster).
  */
  public loadingFinished = new EventEmitter<boolean>();

  private loadingStartedAt: number = 0;

  startLoading() {
    this.loadingStarted.emit();
    this.loadingStartedAt = Date.now();
  }

  /**
   * Ends loading.
   * If the time was less than 1 second, use early end to load.
   */
  endLoading(): void {
    let now = Date.now();
    this.loadingFinished.emit(now - this.loadingStartedAt < 1000);
  }
}
