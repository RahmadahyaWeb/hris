<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $types = [
                ['name' => 'Annual Leave', 'annual_quota' => 12, 'is_paid' => true],
                ['name' => 'Sick Leave', 'annual_quota' => 10, 'is_paid' => true],
                ['name' => 'Unpaid Leave', 'annual_quota' => 0, 'is_paid' => false],
                ['name' => 'Maternity Leave', 'annual_quota' => 90, 'is_paid' => true],
            ];

            foreach ($types as $type) {

                LeaveType::updateOrCreate(
                    ['name' => $type['name']],
                    $type
                );
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
