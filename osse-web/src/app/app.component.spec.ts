import { TestBed } from '@angular/core/testing';
import { AppComponent } from './app.component';
import { WebAudioService } from './shared/player/web-audio.service';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';
import { By } from '@angular/platform-browser';
import { LoadingComponent } from './shared/ui/loading/loading.component';
import { NavigationComponent } from './navigation/navigation.component';
import { ToastContainerComponent } from './toast-container/toast-container.component';
import { PlayerComponent } from './shared/player/player.component';
import { ModalComponent } from './shared/ui/modal/modal.component';

describe('AppComponent', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      providers: [
        provideRouter(routes),
        {
          provide: WebAudioService,
          useClass: class {
            setUp() {};
            setPan(p: number) {};
            getPanValue() {
              return 0.5;
            }
          }
        }
      ]
    }).compileComponents();
  });

  it('should create the app', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    expect(app).toBeTruthy();
  });

  it(`should have the 'osse' title`, () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    expect(app.title).toEqual('Osse');
  });

  it('should have child components', () => {
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    const compiled = fixture.debugElement;

    expect(compiled.query(By.directive(LoadingComponent))).toBeTruthy();
    expect(compiled.query(By.directive(NavigationComponent))).toBeTruthy();
    expect(compiled.query(By.directive(ToastContainerComponent))).toBeTruthy();
    expect(compiled.query(By.directive(PlayerComponent))).toBeTruthy();
    expect(compiled.query(By.directive(ModalComponent))).toBeTruthy();
  });
});
