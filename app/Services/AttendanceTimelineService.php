<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;

class AttendanceTimelineService
{
    public function today($user)
    {
        $schedule = EmployeeSchedule::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $attendance = Attendance::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->first();

        $timeline = [];

        if ($schedule) {

            $timeline[] = [
                'time' => $schedule->shift->start_time,
                'label' => 'Shift Start',
            ];
        }

        if ($attendance && $attendance->checkin_at) {

            $timeline[] = [
                'time' => $attendance->checkin_at,
                'label' => 'Check-in',
            ];
        }

        if ($attendance && $attendance->checkout_at) {

            $timeline[] = [
                'time' => $attendance->checkout_at,
                'label' => 'Check-out',
            ];
        }

        if ($schedule) {

            $timeline[] = [
                'time' => $schedule->shift->end_time,
                'label' => 'Shift End',
            ];
        }

        return $timeline;
    }
}
