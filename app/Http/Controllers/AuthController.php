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
      'username' => 'required|string',
      'password' => 'required|string'
    ]);

    $user = new User($credentials);
    $user->save();
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'username' => ['required'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      return response(status:200);
    }

    return back()->withErrors([
      'username' => 'The provided credentials do not match our records.',
    ])->onlyInput('username');

  }
}
