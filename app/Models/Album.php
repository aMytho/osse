<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use artists;
use tracks;

class Album extends Model
{
    /**
     * @return HasMany<tracks,Album>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * @return BelongsTo<artists,Album>
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}
