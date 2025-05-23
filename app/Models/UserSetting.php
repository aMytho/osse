<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property bool $enable_playback_session
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereEnablePlaybackSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUserId($value)
 */
class UserSetting extends Model
{
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'enable_playback_session',
    ];

    protected $casts = [
        'enable_playback_session' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
