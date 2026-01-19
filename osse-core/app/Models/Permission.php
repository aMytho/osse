<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    public const UPDATED_AT = null;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
