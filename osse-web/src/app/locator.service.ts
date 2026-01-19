import { Injectable, Injector } from '@angular/core';

/**
 * A service that provides services to non angular services/components
 */
@Injectable({
  providedIn: 'root'
})
export class LocatorService {
  public static injector: Injector;
  constructor() { }
}
