<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackSearchRequest;
use App\Jobs\ScanMusic;
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

  public function search(TrackSearchRequest $request)
  {
    $tracks = Track::with('artist')
        ->where('title', 'like', '%' . $request->validated('track', '') . '%')
        ->skip($request->validated('track_offset', 0))
        ->limit(25)
        ->get();

    return response()->json($tracks);
  }

  public function stream(Track $track)
  {
    return response()->file($track->location);
  }

  public function scan()
  {
    ScanMusic::dispatch();
  }
}
