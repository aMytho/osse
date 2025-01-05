<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('scan', function (User $user) {
    return true;
});
