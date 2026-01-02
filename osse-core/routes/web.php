<?php

use App\Http\Middleware\RegistrationCheck;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Sanctum routes. On auth fail, send user here. This redirects to the SPA login.
Route::get('login', [AuthController::class, 'sendToLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])->middleware(RegistrationCheck::class);
