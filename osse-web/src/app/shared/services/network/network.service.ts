import { Injectable } from '@angular/core';
import { ToastService } from '../../../toast-container/toast.service';
import { ModalService } from '../../ui/modal/modal.service';

/**
  * Class handles network operations.
  */
@Injectable({
  providedIn: 'root'
})
export class NetworkService {
  constructor(private notificationService: ToastService, private modelService: ModalService) {
    window.addEventListener('online', () => {
      this.notificationService.info('Internet connection restored.');
    });
    window.addEventListener('offline', () => {
      this.notificationService.info('Internet connection lost.');
    });
  }
}
