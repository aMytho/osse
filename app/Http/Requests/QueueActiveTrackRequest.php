<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class QueueActiveTrackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tracks = Auth::user()->playbackSession->tracks;
        $trackCount = is_array($tracks) ? count($tracks) : 0;

        return [
            'active_track_index' => 'nullable|int|gte:0|lt:'.$trackCount,
            'track_position' => 'nullable|number',
        ];
    }
}
