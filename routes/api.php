<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\TrackController;
use App\Http\Middleware\HTTPCache;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware(StartSession::class);

Route::get('/config/directories', [ConfigController::class, 'directories']);
Route::get('/ping', [ConfigController::class, 'ping']);

Route::get('/albums', [AlbumController::class, 'index'])->middleware(HTTPCache::class);
Route::get('/albums/{album}', [AlbumController::class, 'show'])->middleware(HTTPCache::class);
Route::get('/albums/{album}/tracks', [AlbumController::class, 'tracks'])->middleware(HTTPCache::class);

Route::get('/playlists', [PlaylistController::class, 'index'])->middleware(HTTPCache::class);
Route::post('/playlists', [PlaylistController::class, 'store']);
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show'])->middleware(HTTPCache::class);
Route::patch('/playlists/{playlist}', [PlaylistController::class, 'update']);
Route::delete('/playlists/{playlist}', [PlaylistController::class, 'remove']);
Route::get('/playlists/{playlist}/tracks', [PlaylistController::class, 'tracks'])->middleware(HTTPCache::class);
Route::post('/playlists/{playlist}/tracks/{track}', [PlaylistController::class, 'addTrack']);

Route::get('/artists/{artist}', [ArtistController::class, 'show'])->middleware(HTTPCache::class);

Route::get('/tracks/search', [TrackController::class, 'search']);
Route::post('/tracks/scan', [TrackController::class, 'scan']);
Route::get('/tracks/{track}/stream', [TrackController::class, 'stream'])->middleware(HTTPCache::class);
Route::get('/tracks/{track}/cover', [TrackController::class, 'cover'])->middleware(HTTPCache::class);

