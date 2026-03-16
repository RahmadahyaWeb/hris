<?php

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function checkin(User $user)
    {
        DB::beginTransaction();

        try {

            $schedule = EmployeeSchedule::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            $shift = $schedule->shift;

            $late = now()->diffInMinutes(
                now()->copy()->setTimeFromTimeString($shift->start_time),
                false
            );

            Attendance::create([
                'user_id' => $user->id,
                'date' => today(),
                'checkin_at' => now(),
                'checkin_latitude' => request('latitude'),
                'checkin_longitude' => request('longitude'),
                'late_minutes' => max($late, 0),
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function checkout(User $user)
    {
        DB::beginTransaction();

        try {

            $attendance = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            $schedule = EmployeeSchedule::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->first();

            $shift = $schedule->shift;

            $workMinutes = $attendance->checkin_at->diffInMinutes(now());

            $overtime = now()->diffInMinutes(
                now()->copy()->setTimeFromTimeString($shift->end_time),
                false
            );

            $attendance->update([
                'checkout_at' => now(),
                'checkout_latitude' => request('latitude'),
                'checkout_longitude' => request('longitude'),
                'work_minutes' => $workMinutes,
                'overtime_minutes' => max($overtime, 0),
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
