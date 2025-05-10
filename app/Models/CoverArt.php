<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property string $hash
 * @property string $mime_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\CoverArtFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CoverArt extends Model
{
    /** @use HasFactory<\Database\Factories\CoverArtFactory> */
    use HasFactory;

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
        return 'cover-art/'.$this->hash;
    }
}
