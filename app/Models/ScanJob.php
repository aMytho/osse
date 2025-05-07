<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string $status
 * @property int $total_dirs
 * @property int $scanned_dirs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanDirectory> $directories
 * @property-read int|null $directories_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereScannedDirs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereTotalDirs($value)
 *
 * @mixin \Eloquent
 */
class ScanJob extends Model
{
    protected $fillable = [
        'started_at',
        'finished_at',
        'status',
        'total_dirs',
        'scanned_dirs',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * @return HasMany<ScanDirectory,ScanJob>
     */
    public function directories(): HasMany
    {
        return $this->hasMany(ScanDirectory::class);
    }

    /**
     * Creates a scan job entry and entries for each directory in a pending state.
     * The job is in a running state.
     *
     * @param  Collection<int, string>  $dirsToScan
     * @return ScanJob The job entry.
     */
    public static function createScanJob($dirsToScan)
    {
        $job = self::create([
            'started_at' => now(),
            'status' => 'running',
            'total_dirs' => $dirsToScan->count(),
        ]);

        $jobID = $job->id;

        ScanDirectory::insert($dirsToScan->map(function ($dir) use ($jobID) {
            return [
                'scan_job_id' => $jobID,
                'path' => $dir,
            ];
        })->toArray());

        return $job;
    }
}
