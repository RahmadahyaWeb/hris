<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleService
{
    public function getWeek(int $userId): array
    {
        $start = now()->startOfWeek()->toDateString();
        $end = now()->endOfWeek()->toDateString();

        // schedules + shift (join → NO ELOQUENT HEAVY)
        $rows = DB::table('employee_schedules as es')
            ->join('shifts as s', 's.id', '=', 'es.shift_id')
            ->where('es.user_id', $userId)
            ->whereBetween('es.date', [$start, $end])
            ->select(
                'es.date',
                's.name',
                's.start_time',
                's.end_time',
                's.cross_midnight'
            )
            ->get();

        $scheduleMap = [];

        foreach ($rows as $row) {
            $scheduleMap[$row->date] = $row;
        }

        // leave (minimal)
        $leaves = DB::table('leaves')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->select('start_date', 'end_date')
            ->get();

        $week = [];

        $date = Carbon::parse($start);

        while ($date->toDateString() <= $end) {

            $dateKey = $date->toDateString();

            // === LEAVE CHECK ===
            $isLeave = false;

            foreach ($leaves as $leave) {
                if ($leave->start_date <= $dateKey && $leave->end_date >= $dateKey) {
                    $isLeave = true;
                    break;
                }
            }

            if ($isLeave) {

                $week[] = [
                    'date' => $dateKey,
                    'day' => $date->format('D'),
                    'type' => 'leave',
                ];

            } else {

                $row = $scheduleMap[$dateKey] ?? null;

                if (! $row) {

                    $week[] = [
                        'date' => $dateKey,
                        'day' => $date->format('D'),
                        'type' => 'off',
                    ];

                } else {

                    $startTime = Carbon::parse($row->start_time);
                    $endTime = Carbon::parse($row->end_time);

                    if ($row->cross_midnight && $endTime->lte($startTime)) {
                        $endTime->addDay();
                    }

                    $week[] = [
                        'date' => $dateKey,
                        'day' => $date->format('D'),
                        'type' => 'working',
                        'shift' => $row->name,
                        'start' => $startTime->format('H:i'),
                        'end' => $endTime->format('H:i'),
                        'cross_midnight' => (bool) $row->cross_midnight,
                    ];
                }
            }

            $date->addDay();
        }

        return $week;
    }
}
