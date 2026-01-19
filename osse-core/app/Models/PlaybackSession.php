<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybackSession extends Model
{
    protected $table = 'playback_sessions';

    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'active_track_index', 'track_position', 'tracks'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tracks' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
