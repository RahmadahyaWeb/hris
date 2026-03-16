<?php

namespace Database\Seeders;

use App\Models\AttendanceRule;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            AttendanceRule::firstOrCreate([
                'late_tolerance_minutes' => 10,
                'early_checkout_tolerance' => 10,
                'overtime_after_minutes' => 30,
            ]);

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
