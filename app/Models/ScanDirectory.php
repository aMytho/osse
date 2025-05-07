<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scan_job_id
 * @property string $path
 * @property string $status
 * @property int $files_scanned
 * @property int $files_skipped
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property-read \App\Models\ScanJob $job
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFilesScanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFilesSkipped($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereScanJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereStatus($value)
 *
 * @mixin \Eloquent
 */
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
}
