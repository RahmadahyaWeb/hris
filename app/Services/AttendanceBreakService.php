<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AttendanceBreakService
{
    public function start(Attendance $attendance): void
    {
        DB::beginTransaction();

        try {

            $active = $attendance->breaks()
                ->whereNull('end_at')
                ->exists();

            if ($active) {
                throw new Exception('Break already started');
            }

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'start_at' => now(),
            ]);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function end(Attendance $attendance): void
    {
        DB::beginTransaction();

        try {

            $break = $attendance->breaks()
                ->whereNull('end_at')
                ->latest()
                ->firstOrFail();

            $end = now();

            $duration = Carbon::parse($break->start_at)
                ->diffInMinutes($end);

            $break->update([
                'end_at' => $end,
                'duration_minutes' => $duration,
            ]);

            // update attendance work_minutes
            $this->recalculateWork($attendance);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    private function recalculateWork(Attendance $attendance): void
    {
        if (! $attendance->checkin_at || ! $attendance->checkout_at) {
            return;
        }

        $totalWork = Carbon::parse($attendance->checkin_at)
            ->diffInMinutes(Carbon::parse($attendance->checkout_at));

        $breakMinutes = $attendance->breaks()->sum('duration_minutes');

        $attendance->update([
            'work_minutes' => max($totalWork - $breakMinutes, 0),
        ]);
    }
}
