<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\CoverArtController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\Admin\PermissionController;

Route::get('/ping', [ConfigController::class, 'ping']);

// Sanctum protected (authed users only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/sse', [AuthController::class, 'authorizeSSE']);
    Route::post('/auth/set-password', [AuthController::class, 'setPassword']);

    Route::get('/config', [ConfigController::class, 'allSettings']);
    Route::post('/config', [ConfigController::class, 'storeAllSettings']);
    Route::get('/config/directories', [ConfigController::class, 'directories']);
    Route::get('/config/queue', [ConfigController::class, 'queue']);
    Route::post('/config/queue', [ConfigController::class, 'setQueueSetting']);
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
    Route::delete('/playlists/{playlist}/track-set', [PlaylistController::class, 'removeTracks']);

    Route::get('/artists/{artist}', [ArtistController::class, 'show']);

    Route::get('/tracks/search', [TrackController::class, 'search']);
    Route::get('/tracks/{track}/stream', [TrackController::class, 'stream']);
    Route::get('/tracks/{track}/cover', [TrackController::class, 'cover']);

    Route::get('/cover-art/{cover}', [CoverArtController::class, 'show']);

    Route::get('/scan', [ScanController::class, 'progress'])->name('scan.status');
    Route::post('/scan', [ScanController::class, 'startScan'])->name('scan.start');
    Route::post('/scan/fresh', [ScanController::class, 'startScanFresh'])->name('scan.start-fresh');
    Route::post('/scan/cancel', [ScanController::class, 'cancel'])->name('scan.cancel');
    Route::get('/scan/history', [ScanController::class, 'history'])->name('scan.history');

    Route::get('/queue', [QueueController::class, 'getQueue'])->name('queue.index');
    Route::post('/queue', [QueueController::class, 'setQueue'])->name('queue.update');
    Route::post('/queue/active-track', [QueueController::class, 'setActiveTrack'])->name('queue.active-track.update');
});
