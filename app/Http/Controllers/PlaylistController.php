<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlaylistRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Models\Playlist;
use App\Models\Track;

class PlaylistController extends Controller
{
    public function index()
    {
        return response()->json(Playlist::withCount('tracks')->get());
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

    public function addTrack(Playlist $playlist, Track $track)
    {
        $playlist->tracks()->attach($track);
    }

    public function removeTrack(Playlist $playlist, Track $track)
    {
        $playlist->tracks()->detach($track);
    }
}
