<?php

namespace App\Services\MusicProcessor;

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
            Storage::put('cover-art/' . $this->hash, file_get_contents($this->file->getRealPath()));
            $this->mimeType = Storage::mimeType('cover-art/' . $this->hash) ?? null;
        } else {
            Storage::put('cover-art/' . $this->hash, $this->file->getContents());
            $this->mimeType = $this->file->getMimeType();
        }
    }

    public function getMimeType(): string
    {
        return $this->mimeType ?? 'application/octet-stream';
    }
}
