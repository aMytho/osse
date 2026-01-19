<?php

namespace App\Services\MusicProcessor;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Kiwilan\Audio\Models\AudioCover;
use SplFileInfo;

class ArtFile
{
    public string $hash;

    private ?string $mimeType;

    public function __construct(private SplFileInfo|AudioCover $file, public string $trackFilePath)
    {
        $this->hash = $this->generateHash();
    }

    private function generateHash(): string
    {
        if ($this->file instanceof SplFileInfo) {
            return hash_file('sha256', $this->file->getRealPath());
        } else {
            return hash('sha256', $this->file->getContents());
        }
    }

    public function storeFile(): void
    {
        if ($this->file instanceof SplFileInfo) {
            if (!Storage::disk(config('scan.cover_art_disk'))->put('cover-art/'.$this->hash, file_get_contents($this->file->getRealPath()))) {
                Log::warning('Failed to store album art for ' . $this->trackFilePath);
            }
            $this->mimeType = Storage::disk(config('scan.cover_art_disk'))->mimeType('cover-art/'.$this->hash) ?? null;
        } else {
            if (!Storage::disk(config('scan.cover_art_disk'))->put('cover-art/'.$this->hash, $this->file->getContents())) {
                Log::warning('Failed to store album art for ' . $this->trackFilePath);
            }
            $this->mimeType = $this->file->getMimeType();
        }
    }

    public function getMimeType(): string
    {
        return $this->mimeType ?? 'application/octet-stream';
    }
}
