<?php

namespace App\Services;

use App\Models\ApprovalHistory;
use App\Models\ApprovalStep;
use App\Models\Leave;
use App\Models\LeaveBalance;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveApprovalService
{
    public function approve(Leave $leave, ?int $approverId = null): void
    {
        DB::beginTransaction();

        try {

            if ($leave->status !== 'pending') {
                throw new Exception('Leave request already finalized.');
            }

            $steps = ApprovalStep::where(
                'leave_type_id',
                $leave->leave_type_id
            )
                ->orderBy('step_order')
                ->get();

            if ($steps->isEmpty()) {
                throw new Exception('Approval flow not configured.');
            }

            $currentStep = $leave->current_level;

            if ($currentStep >= $steps->count()) {
                throw new Exception('Approval already completed.');
            }

            $step = $steps[$currentStep];

            $resolver = new ApprovalResolver;

            $approver = match ($step->approver_type) {

                'manager' => $resolver->resolveManager($leave),

                'hr' => $resolver->resolveHr(),

                default => null,

            };

            $approverId ??= Auth::id();

            if (! $approver) {
                throw new Exception('Approver not configured.');
            }

            if ($approver->id !== $approverId) {
                throw new Exception('You are not the assigned approver.');
            }

            ApprovalHistory::create([
                'leave_id' => $leave->id,
                'step' => $currentStep + 1,
                'approved_by' => $approverId,
                'action' => 'approved',
                'acted_at' => now(),
            ]);

            $nextLevel = $currentStep + 1;

            if ($nextLevel >= $steps->count()) {

                $leave->update([
                    'status' => 'approved',
                    'current_level' => $steps->count(),
                ]);

                $this->deductBalance($leave);

            } else {

                $leave->update([
                    'current_level' => $nextLevel,
                ]);

            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function reject(Leave $leave, ?int $approverId = null): void
    {
        DB::beginTransaction();

        try {

            if ($leave->status !== 'pending') {
                throw new Exception('Leave request already finalized.');
            }

            $approverId ??= Auth::id();

            ApprovalHistory::create([
                'leave_id' => $leave->id,
                'step' => $leave->current_level + 1,
                'approved_by' => $approverId,
                'action' => 'rejected',
                'acted_at' => now(),
            ]);

            $leave->update([
                'status' => 'rejected',
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
