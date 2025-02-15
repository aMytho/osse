<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $fillable = ['name'];

    /**
     * @return HasMany<tracks,Artist>
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_artist');
    }

    /**
     * @return HasMany<albums,Artist>
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }
}
