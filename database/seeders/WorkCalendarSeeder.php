<?php

namespace Database\Seeders;

use App\Models\WorkCalendar;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkCalendarSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $start = Carbon::now()->subMonths(2)->startOfMonth();
            $end = Carbon::now()->addMonth()->endOfMonth();

            $date = $start->copy();

            while ($date->lte($end)) {

                $isWeekend = $date->isWeekend();

                WorkCalendar::firstOrCreate(
                    [
                        'date' => $date->toDateString(),
                    ],
                    [
                        'is_holiday' => $isWeekend,
                        'description' => $isWeekend ? 'Weekend' : null,
                    ]
                );

                $date->addDay();
            }

            /**
             * Example national holidays
             */
            $holidays = [
                [
                    'date' => '2026-01-01',
                    'description' => 'New Year',
                ],
                [
                    'date' => '2026-05-01',
                    'description' => 'Labour Day',
                ],
                [
                    'date' => '2026-08-17',
                    'description' => 'Independence Day',
                ],
            ];

            foreach ($holidays as $holiday) {

                WorkCalendar::updateOrCreate(
                    ['date' => $holiday['date']],
                    [
                        'is_holiday' => true,
                        'description' => $holiday['description'],
                    ]
                );
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
