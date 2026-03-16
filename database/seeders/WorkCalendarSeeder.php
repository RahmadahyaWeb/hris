<?php

namespace Database\Seeders;

use App\Models\WorkCalendar;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WorkCalendarSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $start = Carbon::create(now()->year, 2, 1);
            $end = now();

            $date = $start->copy();

            while ($date->lte($end)) {

                WorkCalendar::firstOrCreate([
                    'date' => $date->toDateString(),
                ], [
                    'is_holiday' => $date->isWeekend(),
                    'description' => $date->isWeekend() ? 'Weekend' : null,
                ]);

                $date->addDay();
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
