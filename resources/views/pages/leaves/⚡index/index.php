<?php

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveApprovalService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $leaveId = null;

    public ?int $user_id = null;

    public ?int $leave_type_id = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public int $days = 0;

    public ?string $reason = null;

    public string $status = 'pending';

    public ?int $deleteId = null;

    public ?Leave $selectedLeave = null;

    #[Computed]
    public function leaves()
    {
        $this->authorize('viewAny', Leave::class);

        return Leave::with([
            'user.position.division',
            'leaveType.approvalSteps',
            'histories.approver',
        ])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function users()
    {
        return User::pluck('name', 'id');
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::pluck('name', 'id');
    }

    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'integer', 'min:1'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', Leave::class);

        $this->reset([
            'leaveId',
            'user_id',
            'leave_type_id',
            'start_date',
            'end_date',
            'days',
            'reason',
        ]);

        $this->status = 'pending';

        $this->modal('leave-form')->show();
    }

    public function edit(int $id): void
    {
        $leave = Leave::findOrFail($id);

        $this->authorize('update', $leave);

        $this->leaveId = $leave->id;
        $this->user_id = $leave->user_id;
        $this->leave_type_id = $leave->leave_type_id;
        $this->start_date = $leave->start_date;
        $this->end_date = $leave->end_date;
        $this->days = $leave->days;
        $this->reason = $leave->reason;

        $this->modal('leave-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->leaveId) {

                $leave = Leave::findOrFail($this->leaveId);

                $this->authorize('update', $leave);

                $leave->update([
                    ...$validated,
                    'reason' => $this->reason,
                ]);

                $message = 'Leave updated successfully';

            } else {

                $this->authorize('create', Leave::class);

                Leave::create([
                    ...$validated,
                    'reason' => $this->reason,
                    'status' => 'pending',
                    'current_level' => 0,
                ]);

                $message = 'Leave created successfully';
            }

            DB::commit();

            $this->modal('leave-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset([
                'leaveId',
                'user_id',
                'leave_type_id',
                'start_date',
                'end_date',
                'days',
                'reason',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            $this->dispatch('alert', [
                'title' => 'Error',
                'message' => $e->getMessage(),
                'variant' => 'danger',
            ]);
        }
    }

    public function approve(int $id): void
    {
        DB::beginTransaction();

        try {

            $leave = Leave::with('leaveType.approvalSteps')->findOrFail($id);

            $this->authorize('approve', Leave::class);

            if ($leave->current_level >= $leave->leaveType->approvalSteps->count()) {
                throw new Exception('Approval already completed.');
            }

            $service = new LeaveApprovalService;

            $service->approve($leave);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Leave approved successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Leave approved successfully',
                'variant' => 'success',
            ]);
        }
    }

    public function reject(int $id): void
    {
        DB::beginTransaction();

        try {

            $leave = Leave::findOrFail($id);

            $this->authorize('approve', Leave::class);

            $service = new LeaveApprovalService;

            $service->reject($leave);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Leave rejected successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            $this->dispatch('alert', [
                'title' => 'Error',
                'message' => $e->getMessage(),
                'variant' => 'danger',
            ]);
        }
    }

    public function timeline(int $id): void
    {
        $this->selectedLeave = Leave::with('histories.approver')->findOrFail($id);

        $this->modal('leave-timeline')->show();
    }

    public function confirmDelete(int $id): void
    {
        $leave = Leave::findOrFail($id);

        $this->authorize('delete', $leave);

        $this->deleteId = $id;

        $this->modal('delete-leave')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $leave = Leave::findOrFail($this->deleteId);

            $this->authorize('delete', $leave);

            $leave->delete();

            DB::commit();

            $this->modal('delete-leave')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Leave deleted successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            $this->dispatch('alert', [
                'title' => 'Error',
                'message' => $e->getMessage(),
                'variant' => 'danger',
            ]);
        }
    }
};
