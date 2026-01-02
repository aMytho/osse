<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $active_track_index
 * @property int $track_position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereActiveTrackIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereTrackPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereUserId($value)
 *
 * @property array<array-key, mixed>|null $tracks
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereTracks($value)
 */
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
