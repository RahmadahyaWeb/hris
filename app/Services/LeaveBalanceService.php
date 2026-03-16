<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\LeaveBalance;
use Exception;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function deduct(Leave $leave): void
    {
        DB::beginTransaction();

        try {

            $year = date('Y', strtotime($leave->start_date));

            $balance = LeaveBalance::where([
                'user_id' => $leave->user_id,
                'leave_type_id' => $leave->leave_type_id,
                'year' => $year,
            ])->firstOrFail();

            if ($balance->remaining_days < $leave->days) {
                throw new Exception('Insufficient leave balance.');
            }

            $balance->update([
                'used_days' => $balance->used_days + $leave->days,
                'remaining_days' => $balance->remaining_days - $leave->days,
            ]);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function restore(Leave $leave): void
    {
        DB::beginTransaction();

        try {

            $year = date('Y', strtotime($leave->start_date));

            $balance = LeaveBalance::where([
                'user_id' => $leave->user_id,
                'leave_type_id' => $leave->leave_type_id,
                'year' => $year,
            ])->first();

            if ($balance) {

                $balance->update([
                    'used_days' => $balance->used_days - $leave->days,
                    'remaining_days' => $balance->remaining_days + $leave->days,
                ]);
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
