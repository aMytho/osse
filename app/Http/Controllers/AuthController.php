<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $credentials = $request->validate([
      'username' => 'required|string|unique:users',
      'password' => 'required|string'
    ]);

    $user = new User($credentials);
    $user->save();

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      return response()->json(['message' => 'Account Created'], 201);
    }

    return response(status: 201);
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'username' => ['required'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      return response()->json(['message' => 'Login Successful'], 200);
    }

    // TODO: indicate if a user doesn't exist.
    return response()->json(['message' => 'Invalid Credentials'], 401);
  }

  public function logout(Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
  }

  public function sendToLogin()
  {
    return redirect(config('client_url' . '/login'), 401);
  }

  public function user(Request $request)
  {
    $user = $request->user();

    return response()->json([
      'id' => $user->id,
      'username' => $user->username,
      'broadcastKey' => config('reverb.apps.apps.0.key'),
      'broadcastHost' => config('reverb.apps.apps.0.options.host'),
      'broadcastPort' => config('reverb.apps.apps.0.options.port')
    ]);
  }
}
