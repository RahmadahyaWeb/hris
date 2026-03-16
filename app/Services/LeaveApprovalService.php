<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\LeaveBalance;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    public function approve(Leave $leave): void
    {
        DB::beginTransaction();

        try {

            if ($leave->status !== 'pending') {
                throw new Exception('Leave request already processed.');
            }

            $history = $leave->approval_history ?? [];

            $history[] = [
                'approved_by' => Auth::id(),
                'level' => $leave->current_level,
                'approved_at' => now(),
            ];

            if ($leave->current_level < $leave->approval_level) {

                $leave->update([
                    'current_level' => $leave->current_level + 1,
                    'approval_history' => $history,
                ]);

            } else {

                $leave->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_history' => $history,
                ]);

                $this->deductBalance($leave);
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function reject(Leave $leave): void
    {
        DB::beginTransaction();

        try {

            if ($leave->status !== 'pending') {
                throw new Exception('Leave request already processed.');
            }

            $history = $leave->approval_history ?? [];

            $history[] = [
                'rejected_by' => Auth::id(),
                'level' => $leave->current_level,
                'rejected_at' => now(),
            ];

            $leave->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_history' => $history,
            ]);

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    private function deductBalance(Leave $leave): void
    {
        $balance = LeaveBalance::where([
            'user_id' => $leave->user_id,
            'leave_type_id' => $leave->leave_type_id,
            'year' => date('Y', strtotime($leave->start_date)),
        ])->firstOrFail();

        if ($balance->remaining_days < $leave->days) {
            throw new Exception('Insufficient leave balance.');
        }

        $balance->update([
            'used_days' => $balance->used_days + $leave->days,
            'remaining_days' => $balance->remaining_days - $leave->days,
        ]);
    }
}
