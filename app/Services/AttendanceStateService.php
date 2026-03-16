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

        $shiftStart = Carbon::parse($schedule->date)
            ->setTimeFromTimeString($schedule->shift->start_time);

        $shiftEnd = Carbon::parse($schedule->date)
            ->setTimeFromTimeString($schedule->shift->end_time);

        if ($schedule->shift->cross_midnight || $shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        $checkin = Carbon::parse($attendance->checkin_at);

        $checkout = $attendance->checkout_at
            ? Carbon::parse($attendance->checkout_at)
            : null;

        if ($checkin->lt($shiftStart) && $schedule->shift->cross_midnight) {
            $checkin->addDay();
        }

        if ($checkout && $checkout->lt($shiftStart) && $schedule->shift->cross_midnight) {
            $checkout->addDay();
        }

        $lateMinutes = 0;
        $workMinutes = 0;
        $overtimeMinutes = 0;

        $state = 'on_time';

        if ($checkin->greaterThan($shiftStart)) {

            $lateMinutes = $shiftStart->diffInMinutes($checkin);

            if ($lateMinutes > $lateTolerance) {
                $state = 'late';
            }
        }

        if ($checkout) {

            $workMinutes = $checkin->diffInMinutes($checkout);

            if ($checkout->lessThan($shiftEnd->copy()->subMinutes($earlyTolerance))) {
                $state = 'early_checkout';
            }

            if ($checkout->greaterThan($shiftEnd)) {

                $overtimeMinutes = $shiftEnd->diffInMinutes($checkout);

                if ($overtimeMinutes >= $overtimeAfter) {
                    $state = 'overtime';
                }
            }
        }

        return [
            'state' => $state,
            'late_minutes' => $lateMinutes,
            'work_minutes' => $workMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ];
    }
}
