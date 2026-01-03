import { ResolveFn } from '@angular/router';
import { Album } from '../../shared/services/album/Album';
import { fetcher } from '../../shared/util/fetcher';
import { LoadingService } from '../../shared/ui/loading/loading.service';
import { inject } from '@angular/core';

export const albumViewResolver: ResolveFn<Album> = async (route, _state) => {
  let loadingService: LoadingService = inject(LoadingService);
  loadingService.startLoading();
  let id = route.paramMap.get('id');

  let request = await fetcher(`albums/${id}?tracks=true`);
  if (request.ok) {
    let album = await request.json();
    loadingService.endLoading();
    return new Album(album.data);
  }

  loadingService.endLoading();
  throw "Not Found"
};
