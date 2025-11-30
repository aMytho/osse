<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Permission;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    public function givePermission(User $user, Permission $permission)
    {
        $user->permissions()->attach($permission->id);
    }

    public function removePermission(User $user, Permission $permission)
    {
        $user->permissions()->detach($permission->id);
    }
}
