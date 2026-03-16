<?php

namespace App\Policies;

use App\Models\AttendanceRule;
use App\Models\User;

class AttendanceRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance_rules.view');
    }

    public function view(User $user, AttendanceRule $rule): bool
    {
        return $user->can('attendance_rules.view');
    }

    public function create(User $user): bool
    {
        return $user->can('attendance_rules.create');
    }

    public function update(User $user, AttendanceRule $rule): bool
    {
        return $user->can('attendance_rules.update');
    }

    public function delete(User $user, AttendanceRule $rule): bool
    {
        return $user->can('attendance_rules.delete');
    }
}
