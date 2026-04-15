<?php

use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Traits\AuthorizesCrud;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new #[Title('Leaves')] class extends Component
{
    use AuthorizesCrud;
    use WithoutUrlPagination, WithPagination;

    public $deleteId;

    public function mount()
    {
        $this->authorizeIndex(Leave::class);
    }

    #[Computed()]
    public function leaves()
    {
        $user = Auth::user();

        return Leave::with(['user', 'approvals.approver'])
            ->when(! $user->hasRole('super_admin'), function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('approvals', fn ($q) => $q->where('approver_id', $user->id));
            })
            ->latest()
            ->paginate(10);
    }

    public function approve(int $approvalId)
    {
        $this->transaction(function () use ($approvalId) {

            $approval = LeaveApproval::findOrFail($approvalId);

            $this->authorizeUpdate($approval->leave);

            $approval->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // cek semua approval
            $pending = $approval->leave->approvals()->where('status', 'pending')->exists();

            if (! $pending) {
                $approval->leave->update(['status' => 'approved']);
            }

            Flux::toast(
                heading: 'Success',
                text: 'Leave approved',
                variant: 'success'
            );
        });
    }

    public function reject(int $approvalId)
    {
        $this->transaction(function () use ($approvalId) {

            $approval = LeaveApproval::findOrFail($approvalId);

            $this->authorizeUpdate($approval->leave);

            $approval->update([
                'status' => 'rejected',
                'approved_at' => now(),
            ]);

            $approval->leave->update(['status' => 'rejected']);

            Flux::toast(
                heading: 'Rejected',
                text: 'Leave rejected',
                variant: 'danger'
            );
        });
    }
};
