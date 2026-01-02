<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackSearchRequest;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrackController extends Controller
{
    public function cover(Track $track)
    {
        try {
            if ($track->hasCover()) {
                $track->load('coverArt');

                return Storage::disk(config('scan.cover_art_disk'))->response($track->getCoverUrl())
                    ->setPublic()
                    ->setEtag($track->coverArt->hash)
                    ->setMaxAge(86400)
                    ->setLastModified($track->coverArt->updated_at);
            } else {
                return response()->make(status: 404);
            }
        } catch (\Throwable $th) {
            return response()->make(status: 500);
        }
    }

    public function search(TrackSearchRequest $request)
    {
        $tracks = Track::with('artists')
            ->where('title', 'like', '%'.$request->validated('track', '').'%')
            ->skip($request->validated('track_offset', 0))
            ->limit(75)
            ->get();

        return response()->json($tracks);
    }

    public function stream(Track $track)
    {
        $id = Auth::user()->id;

        // TODO: This is functional, but if the same user is litening to 2 devices with the same track, the client may not use the cached version.
        // Modify so if the user recently preloaded this track (or loaded it normally, return the previous token)
        $keys = Redis::keys('file_access:'.$id.':'.$track->id.':*');
        if ($keys) {
            // Get the token and url. We also refresh the token.
            $token = substr(strrchr($keys[0], ':'), 1);

            Redis::setex('file_access:'.$id.':'.$track->id.':'.$token, 86400, $track->location);

            return response()->json([
                'token' => $token,
                'url' => config('broadcasting.osse-broadcast.stream'),
            ]);
        }

        // Generate a unique token for auth and allow track access.
        $token = Str::random(25);
        // osse_database_file_access:1:1:abc123
        Redis::setex('file_access:'.$id.':'.$track->id.':'.$token, 86400, $track->location);

        // Return the user the token. They already know the track id.
        return response()->json([
            'token' => $token,
            'url' => config('broadcasting.osse-broadcast.stream'),
        ]);
    }
}
