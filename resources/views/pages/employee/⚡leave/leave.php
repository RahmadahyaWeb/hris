<?php

use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $leaveId = null;

    public ?int $leave_type_id = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public int $days = 0;

    public ?string $reason = null;

    public ?Leave $selectedLeave = null;

    protected function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'integer', 'min:1'],
        ];
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::pluck('name', 'id');
    }

    #[Computed]
    public function balances()
    {
        return LeaveBalance::with('leaveType')
            ->where('user_id', Auth::id())
            ->where('year', now()->year)
            ->get();
    }

    #[Computed]
    public function leaves()
    {
        return Leave::with(['leaveType'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate($this->perPage);
    }

    public function create(): void
    {
        $this->reset([
            'leaveId',
            'leave_type_id',
            'start_date',
            'end_date',
            'days',
            'reason',
        ]);

        $this->modal('leave-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            Leave::create([
                'user_id' => Auth::id(),
                ...$validated,
                'reason' => $this->reason,
                'status' => 'pending',
                'current_level' => 0,
            ]);

            DB::commit();

            $this->modal('leave-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Leave submitted successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function timeline(int $id): void
    {
        $this->selectedLeave = Leave::with([
            'leaveType.approvalSteps',
            'histories.approver',
        ])->findOrFail($id);

        $this->modal('leave-timeline')->show();
    }
};
