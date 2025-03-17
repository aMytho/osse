<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoverArt extends Model
{
    protected $fillable = ['hash', 'mime_type'];

    /**
     * @return HasMany<Track,CoverArt>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    public function getCoverLocation(): string
    {
        return 'cover-art/' . $this->hash;
    }
}
