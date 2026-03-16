<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function period(string $startDate, string $endDate, ?int $branchId = null, ?int $divisionId = null)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $users = User::with(['branch', 'position.division'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($divisionId, fn ($q) => $q->whereHas('position', fn ($qq) => $qq->where('division_id', $divisionId)
            )
            )
            ->get();

        $result = [];

        foreach ($users as $user) {

            $workingDays = EmployeeSchedule::where('user_id', $user->id)
                ->whereBetween('date', [$start, $end])
                ->count();

            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$start, $end])
                ->get();

            $present = $attendances->whereNotNull('checkin_at')->count();

            $late = $attendances->where('late_minutes', '>', 0)->count();

            $overtime = $attendances->where('overtime_minutes', '>', 0)->count();

            $absent = max($workingDays - $present, 0);

            $attendanceRate = $workingDays > 0
                ? round(($present / $workingDays) * 100, 2)
                : 0;

            $totalWorkMinutes = $attendances->sum('work_minutes');

            $totalOvertimeMinutes = $attendances->sum('overtime_minutes');

            $result[] = [
                'user' => $user->name,
                'branch' => $user->branch->name ?? '-',
                'division' => $user->position->division->name ?? '-',

                'working_days' => $workingDays,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,

                'overtime_days' => $overtime,

                'work_hours' => round($totalWorkMinutes / 60, 2),
                'overtime_hours' => round($totalOvertimeMinutes / 60, 2),

                'attendance_rate' => $attendanceRate,
            ];
        }

        return collect($result);
    }
}
