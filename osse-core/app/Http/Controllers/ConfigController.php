<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConfigRequest;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ConfigController extends Controller
{
    public function ping()
    {
        return response()->make()->status(200);
    }

    public function directories()
    {
        return config('scan.directories');
    }

    public function queue()
    {
        return response()->json([
            'enabled' => Auth::user()->settings->enable_playback_session,
        ]);
    }

    public function setQueueSetting(Request $request)
    {
        $queue = $request->validate(['queue' => 'required|boolean'])['queue'];

        Auth::user()->settings()->update([
            'enable_playback_session' => $queue,
        ]);
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

    public function allSettings()
    {
        return response()->json([
            'queueEnabled' => Auth::user()->settings->enable_playback_session,
            'directories' => config('scan.directories'),
        ]);
    }

    public function storeAllSettings(StoreConfigRequest $request)
    {
        Auth::user()->settings()->update([
            'enable_playback_session' => $request->validated('enableQueue'),
        ]);
    }
}
