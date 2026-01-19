<?php

namespace App\Enums;

enum ScanDirStatus: string
{
    case Pending = 'pending';
    case Scanning = 'scanning';
    case Scanned = 'scanned';
    case Errored = 'errored';
}
