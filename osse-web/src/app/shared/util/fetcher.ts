import { LocatorService } from "../../locator.service";
import { ConfigService } from "../services/config/config.service";

export async function fetcher(url: string, args: Partial<FetcherArgs> = { method: 'GET', headers: [], body: null, rootURL: null }): Promise<Response> {
  let token = '';
  try {
    token = await getCSRFToken() as string;
  } catch (e) {
    console.error('Failed to get CSRF Token. Check the the server is running and that the URL is correct');
    throw 'CSRF Error';
  }

  // Add the XSRF token to the header list
  let headers = new Headers(args.headers);
  headers.append('X-XSRF-TOKEN', decodeURIComponent(token));
  headers.append('Content-Type', 'application/json');
  headers.append('Accept', 'application/json');

  return fetch((args.rootURL ?? LocatorService.injector.get(ConfigService).get('apiURL')) + url, {
    method: args.method,
    headers: headers,
    body: args.body,
    credentials: 'include'
  });
}

export async function getCSRFToken() {
  let token = getCookie('XSRF-TOKEN');

  if (!token) {
    console.log('Fetching CSRF token.');
    let res = await fetch(LocatorService.injector.get(ConfigService).get('apiURL') + 'sanctum/csrf-cookie', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    });

    if (res.ok) {
      console.log('Fetched token!');
      return getCookie('XSRF-TOKEN');
    }

    throw 'CSRF Error.'
  }

  return token;
}

export function getCookie(name: string) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop()?.split(';').shift();
  return '';
}

export type FetcherArgs = {
  method: string,
  headers: HeadersInit,
  body: BodyInit | null,
  rootURL: string | null
}
