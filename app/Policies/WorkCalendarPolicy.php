<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkCalendar;

class WorkCalendarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('work_calendars.view');
    }

    public function view(User $user, WorkCalendar $calendar): bool
    {
        return $user->can('work_calendars.view');
    }

    public function create(User $user): bool
    {
        return $user->can('work_calendars.create');
    }

    public function update(User $user, WorkCalendar $calendar): bool
    {
        return $user->can('work_calendars.update');
    }

    public function delete(User $user, WorkCalendar $calendar): bool
    {
        return $user->can('work_calendars.delete');
    }
}
