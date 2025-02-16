<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePlaylistRequest;
use App\Http\Requests\UpdatePlaylistRequest;
use App\Models\Playlist;
use App\Models\Track;
use Illuminate\Http\Request;

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
        $playlist->load(['tracks', 'tracks.artists']);
        return response()->json($playlist);
    }

    public function tracks(Playlist $playlist)
    {
        $playlist->load(['tracks', 'tracks.artists']);
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

    public function addTracks(Playlist $playlist, Request $request)
    {
        $validated = $request->validate([
            'track-ids' => 'required|array',
            'track-ids.*' => 'required|integer'
        ]);

        $playlist->tracks()->attach($validated['track-ids']);
    }

    public function removeTrack(Playlist $playlist, Track $track)
    {
        $playlist->tracks()->detach($track);
    }
}
