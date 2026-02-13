import { Component, inject } from '@angular/core';
import { HeaderComponent } from '../../shared/ui/header/header.component';
import { IconComponent } from '../../shared/ui/icon/icon.component';
import { mdiKey } from '@mdi/js';
import { FormsModule } from '@angular/forms';
import { fetcher } from '../../shared/util/fetcher';
import { ToastService } from '../../toast-container/toast.service';

@Component({
  selector: 'app-settings-account',
  imports: [FormsModule, HeaderComponent, IconComponent],
  templateUrl: './settings-account.component.html',
  styles: ``,
})
export class SettingsAccountComponent {
  passwordIcon = mdiKey;
  password: string = '';
  passwordConfirmation: string = '';
  notification = inject(ToastService);

  public async savePassword() {
    if (this.password != this.passwordConfirmation) {
      this.notification.error('Passwords don\'t match');
      return
    }


    let req = await fetcher('auth/set-password', {
      method: 'POST',
      body: JSON.stringify({
        password: this.password,
        passwordConfirmation: this.passwordConfirmation
      })
    });

    if (req.ok) {
      this.notification.info('Password set');
    } else {
      this.notification.error('Password not set. Check that it meets the requirements');
    }
  }
}
