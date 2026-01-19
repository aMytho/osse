import { Injectable } from '@angular/core';
import { ConfigService } from '../services/config/config.service';

@Injectable({
  providedIn: 'root'
})
export class WebAudioService {
  private audioContext = new AudioContext();
  private panner = this.audioContext.createStereoPanner();
  private analyser = this.audioContext.createAnalyser();

  constructor(private configService: ConfigService) { }

  public setUp(audioElement: HTMLAudioElement): HTMLAudioElement {
    const source = this.audioContext.createMediaElementSource(audioElement);

    source.connect(this.analyser);
    // Visualizer
    this.analyser.connect(this.panner);
    // Panning
    this.panner.pan.value = 0;
    this.panner.connect(this.audioContext.destination);
    return audioElement;
  }

  /**
  * Call when playback starts.
  * Web audio is init before user interaction. Some browsers suspend it until interaction.
  * Once audio starts from a user interaction, we can resume it.
  */
  public resumeIfSuspended() {
    if (this.audioContext.state == 'suspended') {
      this.audioContext.resume();
    }
  }

  public setPan(pan: number) {
    this.panner.pan.value = pan;
  }

  public getPanValue(): number {
    return this.panner.pan.value;
  }

  public getFrequencyData(): Uint8Array {
    const smoothFactor = this.configService.get('visualizerSamples');
    const rawData = new Uint8Array(this.analyser.frequencyBinCount);
    this.analyser.getByteFrequencyData(rawData);

    // Downsample by averaging every `smoothFactor` values
    const filteredData = new Uint8Array(rawData.length / smoothFactor);
    for (let i = 0; i < filteredData.length; i++) {
      let sum = 0;
      for (let j = 0; j < smoothFactor; j++) {
        sum += rawData[i * smoothFactor + j];
      }
      filteredData[i] = sum / smoothFactor;
    }

    return filteredData;
  }
}
