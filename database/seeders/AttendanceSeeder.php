<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Services\AttendanceStateService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $schedules = EmployeeSchedule::with('shift')
                ->whereBetween('date', [
                    Carbon::create(now()->year, 2, 1),
                    now(),
                ])
                ->get();

            foreach ($schedules as $schedule) {

                /*
                |------------------------------------------------------------
                | Build shift start & end safely
                |------------------------------------------------------------
                */

                $date = Carbon::parse($schedule->date)->startOfDay();

                $shiftStart = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->start_time);

                $shiftEnd = $date->copy()
                    ->setTimeFromTimeString($schedule->shift->end_time);

                /*
                |------------------------------------------------------------
                | Generate realistic attendance time
                |------------------------------------------------------------
                */

                $checkin = $shiftStart->copy()
                    ->addMinutes(rand(-10, 20));

                $checkout = $shiftEnd->copy()
                    ->addMinutes(rand(-20, 60));

                /*
                |------------------------------------------------------------
                | Create attendance
                |------------------------------------------------------------
                */

                $attendance = Attendance::create([

                    'user_id' => $schedule->user_id,

                    'date' => $schedule->date,

                    'checkin_at' => $checkin,

                    'checkout_at' => $checkout,

                ]);

                /*
                |------------------------------------------------------------
                | Calculate attendance state
                |------------------------------------------------------------
                */

                $stateService = new AttendanceStateService;

                $state = $stateService->resolve(
                    $attendance,
                    $schedule
                );

                /*
                |------------------------------------------------------------
                | Calculate minutes
                |------------------------------------------------------------
                */

                $lateMinutes = $checkin->greaterThan($shiftStart)
                    ? $shiftStart->diffInMinutes($checkin)
                    : 0;

                $workMinutes = $checkin->diffInMinutes($checkout);

                $overtimeMinutes = $checkout->greaterThan($shiftEnd)
                    ? $shiftEnd->diffInMinutes($checkout)
                    : 0;

                /*
                |------------------------------------------------------------
                | Update attendance
                |------------------------------------------------------------
                */

                $attendance->update([

                    'state' => $state['state'],

                    'late_minutes' => $lateMinutes,

                    'work_minutes' => $workMinutes,

                    'overtime_minutes' => $overtimeMinutes,

                ]);

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
