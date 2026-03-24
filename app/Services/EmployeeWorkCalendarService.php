<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeWorkCalendarService
{
    public function generate(int $userId, int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // === SCHEDULE (JOIN SHIFT) ===
        $schedules = DB::table('employee_schedules as es')
            ->join('shifts as s', 's.id', '=', 'es.shift_id')
            ->where('es.user_id', $userId)
            ->whereBetween('es.date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'es.date',
                's.name',
                's.start_time',
                's.end_time',
                's.cross_midnight'
            )
            ->get()
            ->keyBy('date');

        // === LEAVES ===
        $leaves = DB::table('leaves')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->select('start_date', 'end_date')
            ->get();

        // === HOLIDAYS ===
        $holidays = DB::table('work_calendars')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('is_holiday', true)
            ->pluck('date')
            ->toArray();

        $days = [];

        $date = $start->copy();

        while ($date->lte($end)) {

            $key = $date->toDateString();

            // === PRIORITY: LEAVE ===
            $isLeave = false;

            foreach ($leaves as $leave) {
                if ($leave->start_date <= $key && $leave->end_date >= $key) {
                    $isLeave = true;
                    break;
                }
            }

            if ($isLeave) {

                $days[] = [
                    'date' => $key,
                    'day' => $date->day,
                    'type' => 'leave',
                ];

                $date->addDay();

                continue;
            }

            // === HOLIDAY ===
            if (in_array($key, $holidays)) {

                $days[] = [
                    'date' => $key,
                    'day' => $date->day,
                    'type' => 'holiday',
                ];

                $date->addDay();

                continue;
            }

            // === WORKING ===
            $schedule = $schedules->get($key);

            if ($schedule) {

                $startTime = Carbon::parse($schedule->start_time);
                $endTime = Carbon::parse($schedule->end_time);

                if ($schedule->cross_midnight && $endTime->lte($startTime)) {
                    $endTime->addDay();
                }

                $days[] = [
                    'date' => $key,
                    'day' => $date->day,
                    'type' => 'working',
                    'shift' => $schedule->name,
                    'start' => $startTime->format('H:i'),
                    'end' => $endTime->format('H:i'),
                    'cross_midnight' => (bool) $schedule->cross_midnight,
                ];

            } else {

                $days[] = [
                    'date' => $key,
                    'day' => $date->day,
                    'type' => 'off',
                ];
            }

            $date->addDay();
        }

        return $this->formatToCalendarGrid($days, $start);
    }

    private function formatToCalendarGrid(array $days, Carbon $start): array
    {
        $grid = [];

        $firstDayOfWeek = $start->copy()->startOfMonth()->dayOfWeekIso; // 1-7

        // empty slots sebelum tanggal 1
        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $grid[] = null;
        }

        foreach ($days as $day) {
            $grid[] = $day;
        }

        return $grid;
    }
}
