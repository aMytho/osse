<?php

namespace App\Services\MusicProcessor;

use Kiwilan\Audio\Models\AudioCover;
use SplFileInfo;

class ArtFile
{
    public function __construct(private SplFileInfo|AudioCover $file)
    {
    }

    public function generateHash()
    {

    }

    public function storeFile()
    {

    }

    public function getFileData()
    {}
}
