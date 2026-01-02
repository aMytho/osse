import { Injectable } from '@angular/core';
import { environment } from '../../../../environments/environment';
import { OsseConfig } from './config';

@Injectable({
  providedIn: 'root'
})
export class ConfigService {
  private config!: OsseConfig;

  constructor() {
    // Get the ENV and populate any variables. Localstorage has priority
    this.config = {
      apiURL: '/api/',
      version: environment.version,
      showCoverBackgrounds: Boolean(localStorage.getItem('showCoverBackgrounds') ?? environment.showCoverBackgrounds),
      showVisualizer: Boolean(localStorage.getItem('showVisualizer') ?? environment.showVisualizer),
      visualizerSamples: Number(localStorage.getItem('visualizerSamples') ?? environment.visualizerSamples),

      // Below keys are sources from the server so we give them default values.
      // They shouldn't be read until we get the user settings in the login process.
      queue: true,
      userID: -1,
    };
  }

  public get<T extends keyof OsseConfig>(key: T, defaultVal?: any): OsseConfig[T] {
    return this.config[key] ?? defaultVal ?? null;
  }

  public save<T extends keyof OsseConfig>(key: T, val: OsseConfig[T]) {
    localStorage.setItem(key, String(val));
    this.config[key] = val;
  }

  /**
   * Saves many entries into the config.
   */
  public saveMany(conf: Partial<OsseConfig>) {
    this.config = { ...this.config, ...conf };
    for (const key in conf) {
      if (conf.hasOwnProperty(key)) {
        localStorage.setItem(key, String(conf[key as keyof OsseConfig]));
      }
    }
  }

  /**
  * Sets all keys/values from an object to the current config.
  * Keys that are in the current conf but not in the new one are unmodified.
  *
  * Used to populate account config.
  */
  public overrideConfig(conf: Partial<OsseConfig>) {
    this.config = { ...this.config, ...conf };
  }
}
