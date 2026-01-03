<?php

namespace App\Http\Controllers;

use App\Models\Artist;

class ArtistController extends Controller
{
    public function show(Artist $artist)
    {
        return response()->json($artist);
    }
}
