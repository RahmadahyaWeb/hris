<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use Exception;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function checkin($user): Attendance
    {
        DB::beginTransaction();

        try {

            $schedule = EmployeeSchedule::with('shift')
                ->where('user_id', $user->id)
                ->whereDate('date', today())
                ->first();

            if (! $schedule) {
                throw new Exception('No work schedule found for today.');
            }

            $existing = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->first();

            if ($existing && $existing->checkin_at) {
                throw new Exception('You have already checked in today.');
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => today(),
                'checkin_at' => now(),
            ]);

            /**
             * Hitung state awal (late / on_time)
             */
            $stateService = new AttendanceStateService;

            $result = $stateService->resolve(
                $attendance->fresh(),
                $schedule
            );

            $attendance->update([
                'state' => $result['state'],
                'late_minutes' => $result['late_minutes'],
            ]);

            DB::commit();

            return $attendance;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function checkout($user): Attendance
    {
        DB::beginTransaction();

        try {

            $attendance = Attendance::where([
                'user_id' => $user->id,
                'date' => today(),
            ])->firstOrFail();

            if (! $attendance->checkin_at) {
                throw new Exception('Check-in record not found.');
            }

            if ($attendance->checkout_at) {
                throw new Exception('You have already checked out today.');
            }

            $attendance->update([
                'checkout_at' => now(),
            ]);

            $schedule = EmployeeSchedule::with('shift')
                ->where('user_id', $user->id)
                ->whereDate('date', today())
                ->first();

            if ($schedule) {

                $stateService = new AttendanceStateService;

                $result = $stateService->resolve(
                    $attendance->fresh(),
                    $schedule
                );

                $attendance->update([
                    'state' => $result['state'],
                    'late_minutes' => $result['late_minutes'],
                    'work_minutes' => $result['work_minutes'],
                    'overtime_minutes' => $result['overtime_minutes'],
                ]);
            }

            DB::commit();

            return $attendance;

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
