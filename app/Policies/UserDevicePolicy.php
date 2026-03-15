<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDevice;

class UserDevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user_devices.view');
    }

    public function view(User $user, UserDevice $device): bool
    {
        return $user->can('user_devices.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user_devices.create');
    }

    public function update(User $user, UserDevice $device): bool
    {
        return $user->can('user_devices.update');
    }

    public function delete(User $user, UserDevice $device): bool
    {
        return $user->can('user_devices.delete');
    }
}
