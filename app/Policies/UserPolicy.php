<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('users.view');
    }

    public function view(User $authUser, User $user): bool
    {
        if ($authUser->can('users.view')) {
            return true;
        }

        return $authUser->id === $user->id;
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('users.create');
    }

    public function update(User $authUser, User $user): bool
    {
        if ($authUser->can('users.update')) {
            return true;
        }

        return $authUser->id === $user->id;
    }

    public function delete(User $authUser, User $user): bool
    {
        if (! $authUser->can('users.delete')) {
            return false;
        }

        if ($authUser->id === $user->id) {
            return false;
        }

        return true;
    }

    public function restore(User $authUser, User $user): bool
    {
        return $authUser->can('users.restore');
    }

    public function forceDelete(User $authUser, User $user): bool
    {
        return $authUser->can('users.force-delete');
    }
}
