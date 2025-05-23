<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueActiveTrackRequest;
use App\Http\Requests\QueueRequest;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function getQueue()
    {
        $session = Auth::user()->playbackSession;

        if ($session->tracks) {
            return response()->json([
                'trackIndex' => $session->active_track_index,
                'trackPosition' => $session->track_position,
                'tracks' => Track::whereIn('id', $session->tracks)
                    ->with('artists')
                    ->get(),
            ]);

        } else {
            return response()->json([
                'trackIndex' => $session->active_track_index,
                'trackPosition' => $session->track_position,
                'tracks' => [],
            ]);
        }

    }

    public function setQueue(QueueRequest $request)
    {
        // Clear the old session
        $trackIds = $request->validated('ids', []);
        $session = Auth::user()->playbackSession;

        // Set the new session
        $session->update([
            'tracks' => $trackIds,
            'active_track_index' => $request->validated('active_track', $trackIds[0] ?? null),
        ]);
    }

    /**
     * Sets the active track and the position in that track (in seconds).
     */
    public function setActiveTrack(QueueActiveTrackRequest $request)
    {
        Auth::user()
            ->playbackSession()
            ->update($request->validated());
    }
}
