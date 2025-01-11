<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use albums;
use artists;

class Track extends Model
{
    /**
    * Get the attributes that should be cast.
    *
    * @return array<string, string>
    */
    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime'
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
     * @return BelongsTo<artists,Track>
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * @return BelongsTo<CoverArt,Track>
     */
    public function coverArt(): BelongsTo
    {
        return $this->belongsTo(CoverArt::class);
    }
}
