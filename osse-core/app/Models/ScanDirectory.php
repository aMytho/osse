<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanDirectory extends Model
{
    protected $fillable = [
        'scan_job_id',
        'path',
        'status',
        'files_scanned',
        'files_skipped',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo<ScanJob,ScanDirectory>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(ScanJob::class, 'scan_job_id');
    }

    /**
     * Returns the model in camel case format for broadcasting.
     *
     * @return array
     */
    public function toBroadcastArray()
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'status' => $this->status,
            'filesScanned' => $this->files_scanned,
            'filesSkipped' => $this->files_skipped,
        ];
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ScanError::class, 'scan_directory_id');
    }
}
