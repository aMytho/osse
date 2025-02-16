<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackSearchRequest;
use App\Models\Track;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
  public function cover(Track $track)
  {
    try {
      if ($track->hasCover()) {
        $track->load('coverArt');
        return response()->make(content: Storage::get($track->getCoverUrl()))->header('Content-Type', $track->coverArt->mime_type);
      } else {
        return response()->make(status:404);
      }
    } catch (\Throwable $th) {
      return response()->make(status:404);
    }
  }

  public function search(TrackSearchRequest $request)
  {
      $tracks = Track::with('artists')
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
