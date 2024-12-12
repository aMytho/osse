<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    protected $fillable = ['name'];

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class);
    }
}
