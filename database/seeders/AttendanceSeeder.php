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

            $schedules = EmployeeSchedule::with(['shift', 'user'])
                ->whereBetween('date', [
                    now()->subMonths(2)->startOfDay(),
                    now()->endOfDay(),
                ])
                ->get();

            $stateService = new AttendanceStateService;

            foreach ($schedules as $schedule) {

                $shiftStart = Carbon::parse($schedule->date)
                    ->setTimeFromTimeString($schedule->shift->start_time);

                $shiftEnd = Carbon::parse($schedule->date)
                    ->setTimeFromTimeString($schedule->shift->end_time);

                /**
                 * Simulate attendance probability
                 */
                if (fake()->boolean(90) === false) {
                    continue;
                }

                /**
                 * Random checkin variation
                 */
                $checkin = $shiftStart->copy()->addMinutes(fake()->numberBetween(-10, 30));

                /**
                 * Random checkout variation
                 */
                $checkout = $shiftEnd->copy()->addMinutes(fake()->numberBetween(-20, 60));

                $attendance = Attendance::create([
                    'user_id' => $schedule->user_id,
                    'date' => $schedule->date,
                    'checkin_at' => $checkin,
                    'checkout_at' => $checkout,
                ]);

                $result = $stateService->resolve($attendance, $schedule);

                $attendance->update([
                    'state' => $result['state'],
                    'late_minutes' => $result['late_minutes'],
                    'work_minutes' => $result['work_minutes'],
                    'overtime_minutes' => $result['overtime_minutes'],
                ]);
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
