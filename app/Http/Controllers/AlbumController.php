<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResponse;
use App\Models\Album;
use App\Models\Track;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('tracks') == true) {
            $albums = Album::with('tracks')->get();
        } else {
            $albums =  Album::all();
        }

        return response()->json($albums);
    }

    public function show(Album $album, Request $request)
    {
        if ($request->get('tracks') == true) {
            $album->load('tracks');
        }

        return new AlbumResponse($album);
    }

    public function tracks(Album $album)
    {
        return response()->json(Track::where('album_id', $album->id)->get());
    }
}
