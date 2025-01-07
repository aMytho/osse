<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackSearchRequest;
use App\Models\Track;
use Illuminate\Support\Facades\File;
use Kiwilan\Audio\Audio;

class TrackController extends Controller
{
  public function cover(Track $track)
  {
    try {
      if (File::exists($track->location)) {
        $file = Audio::read($track->location);
        if ($file && $file->hasCover()) {
          return response()->make()->setContent($file->getCover()->getContents());
        }

        return response()->make(status:503);
      }
    } catch (\Throwable $th) {
        return response()->make(status:503);
    }
    return response()->make(status:404);
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
}
