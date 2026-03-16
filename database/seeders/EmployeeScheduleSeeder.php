<?php

namespace Database\Seeders;

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
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

            $shift = Shift::firstOrFail();

            $users = User::limit(5)->get();

            $start = Carbon::create(now()->year, 2, 1);
            $end = now();

            foreach ($users as $user) {

                $date = $start->copy();

                while ($date->lte($end)) {

                    if (! $date->isWeekend()) {

                        EmployeeSchedule::firstOrCreate([
                            'user_id' => $user->id,
                            'date' => $date->toDateString(),
                        ], [
                            'shift_id' => $shift->id,
                        ]);

                    }

                    $date->addDay();
                }

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
