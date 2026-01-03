import { Routes } from '@angular/router';
import { HomeComponent } from './home/home.component';
import { albumViewResolver } from './albums/view/album-view.resolver';
import { LoginComponent } from './login/login.component';
import { isLoggedIn } from './shared/services/auth/auth.guard';

export const routes: Routes = [
  {
    path: 'tracks',
    loadComponent: () => import('./track-list/track-list.component').then(c => c.TrackListComponent),
    canActivate: [isLoggedIn],
    title: 'Osse - Track Search'
  },
  {
    path: 'albums',
    loadComponent: () => import('./albums/albums.component').then(c => c.AlbumsComponent),
    canActivate: [isLoggedIn],
    title: 'Osse - Albums'
  },
  {
    path: 'albums/view/:id',
    loadComponent: () => import('./albums/view/view.component').then(c => c.ViewComponent),
    resolve: {
      album: albumViewResolver
    },
    canActivate: [isLoggedIn],
    title: 'Osse - Albums'
  },
  {
    path: 'playlists',
    loadComponent: () => import('./playlist/playlist.component').then(c => c.PlaylistComponent),
    canActivate: [isLoggedIn],
    title: 'Osse - Playlists'
  },
  {
    path: 'playlists/view/:id',
    loadComponent: () => import('./playlist/playlist-view/playlist-view.component').then(c => c.PlaylistViewComponent),
    canActivate: [isLoggedIn],
    title: 'Osse - Playlists'
  },
  {
    path: 'settings',
    loadComponent: () => import('./settings/settings.component').then(c => c.SettingsComponent),
    canActivate: [isLoggedIn],
    title: 'Osse - Settings'
  },
  {
    path: 'home',
    component: HomeComponent,
    canActivate: [isLoggedIn],
    title: 'Osse - Player'
  },
  {
    path: 'login',
    component: LoginComponent,
    title: 'Osse - Login'
  },
  {
    path: 'register',
    loadComponent: () => import('./registration/registration.component').then(c => c.RegistrationComponent),
    title: 'Osse - Register'
  },
  {
    path: "**",
    redirectTo: "home",
  }
];
