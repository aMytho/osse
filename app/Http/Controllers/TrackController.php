<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Kiwilan\Audio\Audio;

class TrackController extends Controller
{
  public function cover(Track $track)
  {
    $file = Audio::read($track->location);
    if ($file && $file->hasCover()) {
      return response()->make()->setContent($file->getCover()->getContents());
    }

    return response()->make()->status(400);
  }

  public function search()
  {
    return response()->json(Track::all());
  }

  public function stream(Track $track)
  {
    return response()->file($track->location);
  }
}
