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

    /*
    |--------------------------------------------------------------------------
    | SUMMARY (BASED ON SCHEDULE + ATTENDANCE)
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function summary()
    {
        $schedules = EmployeeSchedule::with('user')
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->get();

        $attendances = Attendance::whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->get()
            ->keyBy('user_id');

        $present = 0;
        $late = 0;
        $overtime = 0;

        foreach ($schedules as $schedule) {

            $attendance = $attendances->get($schedule->user_id);

            if (! $attendance) {
                continue;
            }

            if ($attendance->checkin_at) {
                $present++;
            }

            if (($attendance->late_minutes ?? 0) > 0) {
                $late++;
            }

            if (($attendance->overtime_minutes ?? 0) > 0) {
                $overtime++;
            }
        }

        $absent = $schedules->count() - $present;

        return [
            'present' => $present,
            'late' => $late,
            'overtime' => $overtime,
            'absent' => $absent < 0 ? 0 : $absent,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE LIST
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ABSENT (BASED ON SCHEDULE)
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function absents()
    {
        $scheduled = EmployeeSchedule::with('user.branch')
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->get();

        $presentIds = Attendance::whereDate('date', $this->date)
            ->pluck('user_id')
            ->toArray();

        return $scheduled->filter(fn ($item) => ! in_array($item->user_id, $presentIds));
    }

    /*
    |--------------------------------------------------------------------------
    | LATE LEADERBOARD (BASED ON MINUTES)
    |--------------------------------------------------------------------------
    */

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
