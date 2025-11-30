<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return HasMany<tracks,Album>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * @return BelongsToMany<artists,Album>
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'album_artist')
            ->withPivot('artist_order');
    }
}
