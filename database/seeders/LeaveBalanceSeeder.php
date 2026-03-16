<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $users = User::all();
            $types = LeaveType::all();

            foreach ($users as $user) {

                foreach ($types as $type) {

                    LeaveBalance::firstOrCreate([
                        'user_id' => $user->id,
                        'leave_type_id' => $type->id,
                        'year' => now()->year,
                    ], [
                        'total_days' => 12,
                        'used_days' => 0,
                        'remaining_days' => 12,
                    ]);

                }

            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
