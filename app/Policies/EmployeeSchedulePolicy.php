<?php

namespace App\Policies;

use App\Models\EmployeeSchedule;
use App\Models\User;

class EmployeeSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employee_schedules.view');
    }

    public function view(User $user, EmployeeSchedule $schedule): bool
    {
        return $user->can('employee_schedules.view');
    }

    public function create(User $user): bool
    {
        return $user->can('employee_schedules.create');
    }

    public function update(User $user, EmployeeSchedule $schedule): bool
    {
        return $user->can('employee_schedules.update');
    }

    public function delete(User $user, EmployeeSchedule $schedule): bool
    {
        return $user->can('employee_schedules.delete');
    }
}
