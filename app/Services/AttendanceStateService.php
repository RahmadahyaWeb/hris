<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;

class AttendanceStateService
{
    public function resolve(Attendance $attendance, EmployeeSchedule $schedule): array
    {
        $rule = new AttendanceRuleService;

        $lateTolerance = $rule->lateTolerance();
        $earlyTolerance = $rule->earlyCheckoutTolerance();
        $overtimeAfter = $rule->overtimeAfter();

        /*
        |------------------------------------------------------------
        | BASE DATE (PENTING)
        |------------------------------------------------------------
        */

        $baseDate = Carbon::parse($schedule->date);

        $shiftStart = $baseDate->copy()
            ->setTimeFromTimeString($schedule->shift->start_time);

        $shiftEnd = $baseDate->copy()
            ->setTimeFromTimeString($schedule->shift->end_time);

        /*
        |------------------------------------------------------------
        | CROSS MIDNIGHT FIX
        |------------------------------------------------------------
        */

        if ($schedule->shift->cross_midnight && $shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        $checkin = Carbon::parse($attendance->checkin_at);

        $checkout = $attendance->checkout_at
            ? Carbon::parse($attendance->checkout_at)
            : null;

        $lateMinutes = 0;
        $workMinutes = 0;
        $overtimeMinutes = 0;

        /*
        |------------------------------------------------------------
        | NORMALIZE CHECKIN (UNTUK OVERNIGHT)
        |------------------------------------------------------------
        */

        if ($schedule->shift->cross_midnight && $checkin->lt($shiftStart)) {
            $checkin->addDay();
        }

        /*
        |------------------------------------------------------------
        | LATE
        |------------------------------------------------------------
        */

        if ($checkin->greaterThan($shiftStart)) {
            $lateMinutes = $shiftStart->diffInMinutes($checkin);
        }

        /*
        |------------------------------------------------------------
        | WORK & OVERTIME
        |------------------------------------------------------------
        */

        if ($checkout) {

            if ($checkout->lt($checkin)) {
                $checkout->addDay();
            }

            $workMinutes = $checkin->diffInMinutes($checkout);

            if ($checkout->greaterThan($shiftEnd)) {
                $overtimeMinutes = $shiftEnd->diffInMinutes($checkout);
            }
        }

        /*
        |------------------------------------------------------------
        | STATE (UI ONLY)
        |------------------------------------------------------------
        */

        if ($overtimeMinutes >= $overtimeAfter) {
            $state = 'overtime';
        } elseif ($lateMinutes > $lateTolerance) {
            $state = 'late';
        } elseif ($checkout && $checkout->lt($shiftEnd->copy()->subMinutes($earlyTolerance))) {
            $state = 'early_checkout';
        } else {
            $state = 'on_time';
        }

        return [
            'state' => $state,
            'late_minutes' => $lateMinutes,
            'work_minutes' => $workMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ];
    }
}
