<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;

class ApprovalResolver
{
    public function resolveManager(Leave $leave): ?User
    {
        $employee = $leave->user;

        $managerPosition = $employee->position->parent;

        if (! $managerPosition) {
            return null;
        }

        return User::where('position_id', $managerPosition->id)->first();
    }

    public function resolveHr(): ?User
    {
        return User::whereHas('position', function ($q) {
            $q->where('title', 'HR Manager');
        })->first();
    }
}
