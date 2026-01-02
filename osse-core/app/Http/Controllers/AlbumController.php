<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResponse;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('tracks') == true) {
            // The value of albums is this json encoded. Data is entered after scan.
            // $albums = Album::with(['tracks', 'tracks.artists', 'artists'])->get();
            return response(Cache::get('albumsWithTracks', []));
        } else {
            return response()->json(Album::all());
        }
    }

    public function show(Album $album, Request $request)
    {
        if ($request->get('tracks') == true) {
            $album->load(['tracks', 'tracks.artists', 'artists']);
        }

        return new AlbumResponse($album);
    }

    public function tracks(Album $album)
    {
        return response()->json(Track::where('album_id', $album->id)->get());
    }
}
