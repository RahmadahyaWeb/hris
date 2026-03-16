<?php

namespace App\Services;

use App\Models\WorkCalendar;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HolidayImportService
{
    public function import(int $year): void
    {
        DB::beginTransaction();

        try {

            $response = Http::get(
                "https://date.nager.at/api/v3/PublicHolidays/$year/ID"
            );

            foreach ($response->json() as $holiday) {

                WorkCalendar::updateOrCreate(
                    ['date' => $holiday['date']],
                    [
                        'is_holiday' => true,
                        'description' => $holiday['localName'],
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
