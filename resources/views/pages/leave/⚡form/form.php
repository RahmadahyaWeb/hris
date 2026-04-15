<?php

use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Models\User;
use App\Traits\AuthorizesCrud;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    use AuthorizesCrud, AuthorizesRequests;

    public ?Leave $leave = null;

    public $type = 'annual';

    public $start_date;

    public $end_date;

    public $reason;

    public function mount(?Leave $leave = null)
    {
        if ($leave && $leave->exists) {
            $this->authorizeUpdate($leave);

            $this->leave = $leave;

            $this->type = $leave->type;
            $this->start_date = $leave->start_date;
            $this->end_date = $leave->end_date;
            $this->reason = $leave->reason;
        } else {
            $this->authorizeStore(Leave::class);
        }
    }

    public function save()
    {
        $this->validate([
            'type' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        return $this->transaction(function () {

            $leave = Leave::create([
                'user_id' => Auth::id(),
                'type' => $this->type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'reason' => $this->reason,
            ]);

            // generate approval sederhana (2 level)
            $approvers = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->take(2)->get();

            foreach ($approvers as $i => $approver) {
                LeaveApproval::create([
                    'leave_id' => $leave->id,
                    'approver_id' => $approver->id,
                    'level' => $i + 1,
                ]);
            }

            Flux::toast(
                heading: 'Success',
                text: 'Leave submitted',
                variant: 'success'
            );

            $this->redirect(route('leaves.index'), navigate: true);
        });
    }
};
