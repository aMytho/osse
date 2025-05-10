<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanError extends Model
{
    protected $fillable = [
        'scan_directory_id',
        'error',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo<ScanJob,ScanDirectory>
     */
    public function directory(): BelongsTo
    {
        return $this->belongsTo(ScanDirectory::class, 'scan_directory_id');
    }
}
