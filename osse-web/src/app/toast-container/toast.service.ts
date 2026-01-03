import { effect, Injectable, signal } from '@angular/core';
import { v4 as uuid } from "uuid";

@Injectable({
  providedIn: 'root'
})
export class ToastService {
  public toasts = signal<ToastMessage[]>([]);
  constructor() {
    // When a toast is closed, remove it after 400ms (this gives time for the opacity animation)
    effect(() => {
      this.toasts();
      setTimeout(() => this.toasts.update((arr) => arr.filter((t) => !t.closed)), 400);
    })
  }

  public info(message: string) {
    let toast = new ToastMessage(message, NotifyType.Info, uuid(), 6)
    this.toasts.update((t) => [...t, toast]);
    setTimeout(() => this.removeToast(toast.id), 6000);
  }

  public error(message: string) {
    let toast = new ToastMessage(message, NotifyType.Error, uuid(), 7);
    this.toasts.update((t) => [...t, toast]);
    setTimeout(() => this.removeToast(toast.id), 7000);
    console.error(toast);
  }

  public removeToast(id: string) {
    this.toasts.update((arr) => arr.map((t) => {
      if ((t.id) == id) {
        t.closed = true;
      }

      return t;
    }));
  }
}

export class ToastMessage {
  public closed: boolean = false;

  constructor(public message: string, public type: NotifyType, public id: string, public duration: number = 6) { }
}

export enum NotifyType {
  Info,
  Error
}

