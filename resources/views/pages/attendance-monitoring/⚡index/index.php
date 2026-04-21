<?php

use App\Models\Attendance;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new #[Title('Attendance Monitoring')] class extends Component
{
    use WithoutUrlPagination, WithPagination;

    public $startDate;

    public $endDate;

    public $status;

    public function mount()
    {
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
    }

    #[Computed]
    public function attendances()
    {
        return Attendance::with([
            'user.employeeAssignments.department',
            'user.employeeAssignments.position',
        ])
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('date')
            ->paginate(10);
    }

    #[Computed]
    public function summary()
    {
        $query = Attendance::query()
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate));

        return [
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'leave' => (clone $query)->where('status', 'leave')->count(),
            'holiday' => (clone $query)->where('status', 'holiday')->count(),
        ];
    }
};
