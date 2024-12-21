<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Sanctum routes. On auth fail, send user here. This redirects to the SPA login.
Route::get('login', [AuthController::class, 'sendToLogin']);
