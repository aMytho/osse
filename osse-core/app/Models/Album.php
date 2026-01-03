<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int|null $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Artist> $artists
 * @property-read int|null $artists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\AlbumFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereYear($value)
 * @mixin \Eloquent
 */
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
