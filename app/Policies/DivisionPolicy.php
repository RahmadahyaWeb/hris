<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;

class DivisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('divisions.view');
    }

    public function view(User $user, Division $division): bool
    {
        return $user->can('divisions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('divisions.create');
    }

    public function update(User $user, Division $division): bool
    {
        return $user->can('divisions.update');
    }

    public function delete(User $user, Division $division): bool
    {
        return $user->can('divisions.delete');
    }
}
