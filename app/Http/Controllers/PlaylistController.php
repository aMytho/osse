<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlaylistRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Models\Playlist;

class PlaylistController extends Controller
{
    public function index()
    {
        return response()->json(Playlist::all());
    }

    public function store(CreatePlaylistRequest $request)
    {
        $playlist = new Playlist([
            'name' => $request->validated('name')
        ]);
        $playlist->save();

        return response()->json($playlist);
    }

    public function show(Playlist $playlist)
    {
        $playlist->load('tracks');
        return response()->json($playlist);
    }

    public function tracks(Playlist $playlist)
    {
        $playlist->load('tracks');
        return response()->json($playlist->tracks);
    }

    public function update(Playlist $playlist, UpdatePlaylistRequest $request)
    {
        $playlist->name = $request->validated('name');
        $playlist->save();
    }

    public function remove(Playlist $playlist)
    {
        $playlist->delete();
    }
}
