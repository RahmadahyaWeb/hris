<?php

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\EmployeeSchedule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 20;

    public ?int $branch_id = null;

    public ?int $division_id = null;

    public string $date;

    public function mount(): void
    {
        $this->date = today()->toDateString();
    }

    #[Computed]
    public function branches()
    {
        return Branch::pluck('name', 'id');
    }

    #[Computed]
    public function summary()
    {
        $attendance = Attendance::whereDate('date', $this->date);

        if ($this->branch_id) {
            $attendance->whereHas('user', fn ($q) => $q->where('branch_id', $this->branch_id));
        }

        $present = (clone $attendance)->count();

        $late = (clone $attendance)->where('state', 'late')->count();

        $overtime = (clone $attendance)->where('overtime_minutes', '>', 0)->count();

        $scheduledUsers = EmployeeSchedule::whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->count();

        $absent = $scheduledUsers - $present;

        return [
            'present' => $present,
            'late' => $late,
            'overtime' => $overtime,
            'absent' => $absent < 0 ? 0 : $absent,
        ];
    }

    #[Computed]
    public function attendances()
    {
        return Attendance::with(['user.branch', 'user.position.division'])
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function absents()
    {
        $scheduled = EmployeeSchedule::with('user.branch')
            ->whereDate('date', $this->date)
            ->get();

        $presentIds = Attendance::whereDate('date', $this->date)
            ->pluck('user_id')
            ->toArray();

        return $scheduled->filter(fn ($item) => ! in_array($item->user_id, $presentIds));
    }

    #[Computed]
    public function lateLeaderboard()
    {
        return Attendance::with('user')
            ->whereDate('date', $this->date)
            ->where('late_minutes', '>', 0)
            ->orderByDesc('late_minutes')
            ->limit(5)
            ->get();
    }
};
