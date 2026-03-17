<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function period(
        string $startDate,
        string $endDate,
        ?int $branchId = null,
        ?int $divisionId = null
    ) {
        $start = Carbon::parse($startDate)->toDateString();
        $end = Carbon::parse($endDate)->toDateString();

        $users = User::with(['branch', 'position.division'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($divisionId, fn ($q) => $q->whereHas('position', fn ($qq) => $qq->where('division_id', $divisionId)
            )
            )
            ->get();

        $result = [];

        foreach ($users as $user) {

            $schedules = EmployeeSchedule::where('user_id', $user->id)
                ->whereDate('date', '>=', $start)
                ->whereDate('date', '<=', $end)
                ->get();

            $attendances = Attendance::where('user_id', $user->id)
                ->whereDate('date', '>=', $start)
                ->whereDate('date', '<=', $end)
                ->get()
                ->keyBy(fn ($item) => Carbon::parse($item->date)->toDateString());

            $workingDays = $schedules->count();

            $present = 0;
            $late = 0;
            $overtimeDays = 0;
            $totalWorkMinutes = 0;
            $totalOvertimeMinutes = 0;

            foreach ($schedules as $schedule) {

                $dateKey = Carbon::parse($schedule->date)->toDateString();

                $attendance = $attendances->get($dateKey);

                if (! $attendance) {
                    continue;
                }

                // === PRESENT ===
                if ($attendance->is_present) {
                    $present++;
                }

                // === LATE (CONSISTENT) ===
                if ($attendance->is_late) {
                    $late++;
                }

                // === OVERTIME ===
                if ($attendance->is_overtime) {
                    $overtimeDays++;
                }

                $totalWorkMinutes += $attendance->work_minutes ?? 0;
                $totalOvertimeMinutes += $attendance->overtime_minutes ?? 0;
            }

            $absent = max($workingDays - $present, 0);

            $attendanceRate = $workingDays > 0
                ? round(($present / $workingDays) * 100, 2)
                : 0;

            $result[] = [
                'user' => $user->name,
                'branch' => $user->branch->name ?? '-',
                'division' => $user->position->division->name ?? '-',

                'working_days' => $workingDays,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,

                'overtime_days' => $overtimeDays,

                'work_hours' => round($totalWorkMinutes / 60, 2),
                'overtime_hours' => round($totalOvertimeMinutes / 60, 2),

                'attendance_rate' => $attendanceRate,
            ];
        }

        return collect($result);
    }
}
