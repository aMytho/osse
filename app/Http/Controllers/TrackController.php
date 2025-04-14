<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackSearchRequest;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackController extends Controller
{
    public function cover(Track $track)
    {
        try {
            if ($track->hasCover()) {
                $track->load('coverArt');

                return Storage::disk(config('scan.cover_art_disk'))->response($track->getCoverUrl())
                    ->setPublic()
                    ->setEtag($track->coverArt->hash)
                    ->setMaxAge(86400)
                    ->setLastModified($track->coverArt->updated_at);
            } else {
                return response()->make(status: 404);
            }
        } catch (\Throwable $th) {
            return response()->make(status: 500);
        }
    }

    public function search(TrackSearchRequest $request)
    {
        $tracks = Track::with('artists')
            ->where('title', 'like', '%'.$request->validated('track', '').'%')
            ->skip($request->validated('track_offset', 0))
            ->limit(75)
            ->get();

        return response()->json($tracks);
    }

    public function stream(Track $track, Request $request)
    {
        set_time_limit(0);
        $filePath = $track->location;

        if (! file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ];

        if ($request->hasHeader('Range')) {
            return $this->handleRangeRequest($request, $filePath, $fileSize, $mimeType);
        }

        return response()->stream(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }

    private function handleRangeRequest(Request $request, $filePath, $fileSize, $mimeType)
    {
        $range = $request->header('Range');
        preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);

        $start = intval($matches[1]);
        $end = isset($matches[2]) ? intval($matches[2]) : $fileSize - 1;

        if ($end >= $fileSize) {
            $end = $fileSize - 1;
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $length,
            'Content-Range' => "bytes $start-$end/$fileSize",
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ];

        $response = new StreamedResponse(function () use ($filePath, $start, $length) {
            $file = fopen($filePath, 'rb');
            fseek($file, $start);
            $bytesLeft = $length;

            while (! feof($file) && $bytesLeft > 0) {
                $buffer = fread($file, min(8192, $bytesLeft));
                echo $buffer;
                flush();
                $bytesLeft -= strlen($buffer);
            }

            fclose($file);
        }, 206, $headers);

        return $response;
    }
}
