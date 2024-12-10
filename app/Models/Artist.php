<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use albums;
use playlists;
use tracks;

class Artist extends Model
{
    protected $fillable = ['name'];

    /**
     * @return HasMany<tracks,Artist>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany('tracks');
    }

    /**
     * @return HasMany<playlists,Artist>
     */
    public function playlists(): HasMany
    {
        return $this->hasMany('playlists');
    }

    /**
     * @return HasMany<albums,Artist>
     */
    public function albums(): HasMany
    {
        return $this->hasMany('albums');
    }
}
