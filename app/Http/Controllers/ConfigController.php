<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function directories()
    {
        return config('scan.directories');
    }

    public function ping()
    {
        return response()->make()->status(200);
    }
}
