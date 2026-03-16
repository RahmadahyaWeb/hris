<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leaves.view');
    }

    public function view(User $user, Leave $leave): bool
    {
        return $user->can('leaves.view');
    }

    public function create(User $user): bool
    {
        return $user->can('leaves.create');
    }

    public function update(User $user, Leave $leave): bool
    {
        return $user->can('leaves.update');
    }

    public function delete(User $user, Leave $leave): bool
    {
        return $user->can('leaves.delete');
    }

    public function approve(User $user): bool
    {
        return $user->can('leaves.approve');
    }
}
