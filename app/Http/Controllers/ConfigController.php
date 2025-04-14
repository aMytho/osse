<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ConfigController extends Controller
{
    public function directories()
    {
        return config('scan.directories');
    }

    public function ping()
    {
        return response()->make()->status(200);
    }

    public function logs()
    {
        $log = env('LOG_PATH', storage_path('logs/laravel.log'));
        if (! File::exists($log)) {
            return response()->json(['message' => 'Log file not found'], 404);
        }

        $lines = [];
        $file = new \SplFileObject($log, 'r');
        $file->seek(PHP_INT_MAX); // Go to end of file
        $totalLines = $file->key();

        $limit = 500;
        $start = max(0, $totalLines - $limit);

        $file->seek($start);
        while (! $file->eof()) {
            $lines[] = $file->fgets();
        }

        return response(content: implode('', $lines));
    }
}
