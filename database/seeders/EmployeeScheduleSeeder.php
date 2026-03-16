<?php

namespace Database\Seeders;

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkCalendar;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $users = User::all();

            $shift = Shift::first();

            $startDate = now()->subMonths(2)->startOfDay();
            $endDate = now()->endOfDay();

            $period = Carbon::parse($startDate);

            while ($period->lte($endDate)) {

                $calendar = WorkCalendar::whereDate('date', $period)->first();

                if ($calendar && $calendar->is_holiday) {
                    $period->addDay();

                    continue;
                }

                foreach ($users as $user) {

                    EmployeeSchedule::firstOrCreate([
                        'user_id' => $user->id,
                        'date' => $period->toDateString(),
                    ], [
                        'shift_id' => $shift->id,
                    ]);

                }

                $period->addDay();
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
