<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $title
 * @property int $duration
 * @property int $size
 * @property int|null $bitrate
 * @property string $location
 * @property int|null $album_id
 * @property int|null $year
 * @property int|null $track_number
 * @property int|null $disc_number
 * @property int|null $cover_art_id
 * @property \Illuminate\Support\Carbon $scanned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Album|null $album
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Artist> $artists
 * @property-read int|null $artists_count
 * @property-read \App\Models\CoverArt|null $coverArt
 *
 * @method static \Database\Factories\TrackFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereAlbumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCoverArtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereDiscNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereScannedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTrackNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereYear($value)
 *
 * @mixin \Eloquent
 */
class Track extends Model
{
    /** @use HasFactory<\Database\Factories\TrackFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<albums,Track>
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * The artists for the track.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'track_artist')
            ->withPivot('artist_order')
            ->orderBy('artist_order');
    }

    /**
     * @return BelongsTo<CoverArt,Track>
     */
    public function coverArt(): BelongsTo
    {
        return $this->belongsTo(CoverArt::class);
    }

    /**
     * Checks if a track has cover art.
     */
    public function hasCover(): bool
    {
        return ! is_null($this->cover_art_id);
    }

    public function getCoverUrl(): string
    {
        return 'cover-art/'.$this->coverArt?->hash;
    }
}
