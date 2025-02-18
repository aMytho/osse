<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', [ConfigController::class, 'ping']);
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/config/directories', [ConfigController::class, 'directories']);
    Route::get('/config/logs', [ConfigController::class, 'logs']);
    Route::get('/albums', [AlbumController::class, 'index']);
    Route::get('/albums/{album}', [AlbumController::class, 'show']);
    Route::get('/albums/{album}/tracks', [AlbumController::class, 'tracks']);

    Route::get('/playlists', [PlaylistController::class, 'index']);
    Route::post('/playlists', [PlaylistController::class, 'store']);
    Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);
    Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::delete('/playlists/{playlist}', [PlaylistController::class, 'remove']);
    Route::get('/playlists/{playlist}/tracks', [PlaylistController::class, 'tracks']);
    Route::post('/playlists/{playlist}/tracks/{track}', [PlaylistController::class, 'addTrack']);
    Route::post('/playlists/{playlist}/track-set', [PlaylistController::class, 'addTracks']);
    Route::delete('/playlists/{playlist}/tracks/{track}', [PlaylistController::class, 'removeTrack']);

    Route::get('/artists/{artist}', [ArtistController::class, 'show']);

    Route::get('/tracks/search', [TrackController::class, 'search']);
    Route::get('/tracks/{track}/stream', [TrackController::class, 'stream']);
    Route::get('/tracks/{track}/cover', [TrackController::class, 'cover']);

    Route::get('/scan', [ScanController::class, 'progress'])->name('scan.status');
    Route::post('/scan', [ScanController::class, 'startScan'])->name('scan.start');
});
