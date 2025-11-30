<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
