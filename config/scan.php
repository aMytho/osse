<?php

return [
    'directories' => explode(',', env('OSSE_DIRECTORIES', '')),
    'cover_art_disk' => env('COVER_ART_DISK', 'local'), // In testing, cover art gets a custom disk. Otherwise its local.
];
