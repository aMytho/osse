import { Component, OnInit, signal, WritableSignal } from '@angular/core';
import { fetcher } from '../shared/util/fetcher';
import { HeaderComponent } from '../shared/ui/header/header.component';
import { FormsModule } from '@angular/forms';
import { ToastService } from '../toast-container/toast.service';
import { ConfigService } from '../shared/services/config/config.service';
import { Router } from '@angular/router';
import { AuthService } from '../shared/services/auth/auth.service';

@Component({
  selector: 'app-login',
  imports: [HeaderComponent, FormsModule],
  templateUrl: './login.component.html',
  styles: ``
})
export class LoginComponent implements OnInit {
  public username: string = '';
  public password: string = '';
  public serverFound: WritableSignal<boolean> = signal(false);
  public url: WritableSignal<string> = signal(window.location.hostname + ':8000');
  public protocol: WritableSignal<string> = signal('http://');
  public showConnectionInputs: WritableSignal<boolean> = signal(false);
  public waitingForResponse = signal(false);

  constructor(
    private notificationService: ToastService,
    private configService: ConfigService,
    private router: Router,
    private authService: AuthService
  ) { }

  public async saveURL() {
    if (!this.url().endsWith("/")) {
      this.url.update(u => u + "/");
    }

    // Check if the server URL is right.
    try {
      this.waitingForResponse.set(true);
      await fetch(this.protocol().concat(this.url()) + 'api/ping', {
        credentials: 'include'
      });
      // Save the URL
      this.configService.save("apiURL", this.protocol().concat(this.url()));
      this.notificationService.info("URL saved as " + this.configService.get("apiURL"));
      this.serverFound.set(true);
    } catch (e) {
      this.notificationService.error('Failed to reach server. Confirm that the URL is correct.');
    } finally {
      this.waitingForResponse.set(false);
    }
  }

  public async login() {
    if (this.username.length == 0 || this.password.length == 0) {
      this.notificationService.error('You must enter a username and password.');
      return;
    }

    this.waitingForResponse.set(true);

    let res = await fetcher('login', {
      method: 'POST',
      body: JSON.stringify({
        username: this.username,
        password: this.password,
      }),
      rootURL: this.configService.get('apiURL')
    });

    if (res.ok) {
      await this.authService.attemptLogin();
      this.router.navigateByUrl('/home');
    } else {
      this.notificationService.error('Login error. Check that the username and password are correct.');
    }

    this.waitingForResponse.set(false);
  }

  async ngOnInit() {
    // Try to login with the default URL.
    try {
      await fetch(this.configService.get('apiURL') + 'api/ping', {
        credentials: 'include'
      });
      this.serverFound.set(true);
    } catch (e) {
      // This should only happen in dev. If it fails, show the server URL inputs.
      this.notificationService.error('Failed to autodetect server URL. Please enter it.');
      this.showConnectionInputs.set(true);
    }
  }
}
