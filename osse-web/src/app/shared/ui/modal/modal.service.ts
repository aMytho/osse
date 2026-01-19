import { EventEmitter, Injectable, Output } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  @Output() onLoadComponent = new EventEmitter<any>();
  @Output() onShow = new EventEmitter();
  @Output() onClose = new EventEmitter();

  constructor() { }

  public setDynamicModal(component: any, input: {name: string, val: any}[], title: string) {
    this.onLoadComponent.emit([component, title, input]);
  }

  public setStaticModal(component: any, title: string) {
    this.onLoadComponent.emit([component, title]);
  }

  public show() {
    this.onShow.emit();
  }

  public close() {
    this.onClose.emit();
  }
}
