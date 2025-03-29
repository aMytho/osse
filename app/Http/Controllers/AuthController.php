<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

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

  /**
  * Give the user access to SSE by storing the user id in sse-access redis.
  * The value is a random string which is sent as a mock-password.
  * Its secure enough.
  */
  public function authorizeSSE()
  {
    $token = Str::random(25);
    $id = Auth::user()->id;
    $url = config('broadcasting.osse-broadcast.url') . 'sse';
    // Give broadcast permission rights for 60 seconds. They have to connect in that window.
    Redis::setex('sse_access:' . $id, 60, $token);

    // Return the token and the user id.
    // The user should know their ID, but the client side doesn't have a clean way to get that yet.
    return response()->json(['token' => $token, 'userID' => $id, 'url' => $url]);
  }
}
