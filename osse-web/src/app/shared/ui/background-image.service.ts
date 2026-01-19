import { EventEmitter, Injectable } from '@angular/core';
import { ConfigService } from '../services/config/config.service';

@Injectable({
  providedIn: 'root'
})
export class BackgroundImageService {
  public bgChanged = new EventEmitter<string>();
  constructor(private configService: ConfigService) { }

  public setBG(bg: string) {
    if (this.configService.get('showCoverBackgrounds')) {
      this.bgChanged.emit(bg);
    }
  }

  public clearBG() {
    this.bgChanged.emit('#');
  }
}
