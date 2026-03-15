<?php

namespace Database\Seeders;

use App\Models\Shift;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $shifts = [
                [
                    'name' => 'Morning Shift',
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                    'cross_midnight' => false,
                ],
                [
                    'name' => 'Afternoon Shift',
                    'start_time' => '16:00:00',
                    'end_time' => '23:00:00',
                    'cross_midnight' => false,
                ],
                [
                    'name' => 'Night Shift',
                    'start_time' => '23:00:00',
                    'end_time' => '07:00:00',
                    'cross_midnight' => true,
                ],
                [
                    'name' => 'Full Day Shift',
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'cross_midnight' => false,
                ],
            ];

            foreach ($shifts as $shift) {

                Shift::firstOrCreate(
                    ['name' => $shift['name']],
                    $shift
                );

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
