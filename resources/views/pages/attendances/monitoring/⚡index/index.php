<?php

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Division;
use App\Models\EmployeeSchedule;
use App\Services\AttendanceRuleService;
use Carbon\Carbon;
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
    public function divisions()
    {
        return Division::pluck('name', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY (BASED ON SCHEDULE + ATTENDANCE)
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function summary()
    {
        $rule = new AttendanceRuleService;

        $lateTolerance = $rule->lateTolerance();
        $overtimeAfter = $rule->overtimeAfter();

        $schedules = EmployeeSchedule::with(['user.position'])
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->when(
                $this->division_id,
                fn ($q) => $q->whereHas('user.position', fn ($qq) => $qq->where('division_id', $this->division_id))
            )
            ->get();

        $attendances = Attendance::whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->when(
                $this->division_id,
                fn ($q) => $q->whereHas('user.position', fn ($qq) => $qq->where('division_id', $this->division_id))
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

            if (($attendance->late_minutes ?? 0) > $lateTolerance) {
                $late++;
            }

            if (($attendance->overtime_minutes ?? 0) > $overtimeAfter) {
                $overtime++;
            }
        }

        return [
            'present' => $present,
            'late' => $late,
            'overtime' => $overtime,
            'absent' => max($schedules->count() - $present, 0),
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
        $rule = new AttendanceRuleService;

        $lateTolerance = $rule->lateTolerance();
        $overtimeAfter = $rule->overtimeAfter();

        return Attendance::with([
            'user.branch',
            'user.position.division',
            'breaks',
        ])
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->when(
                $this->division_id,
                fn ($q) => $q->whereHas('user.position', fn ($qq) => $qq->where('division_id', $this->division_id))
            )
            ->latest()
            ->paginate($this->perPage)
            ->through(function ($attendance) use ($lateTolerance, $overtimeAfter) {

                $statuses = [];

                if (($attendance->late_minutes ?? 0) > $lateTolerance) {
                    $statuses[] = ['label' => 'Late', 'color' => 'yellow'];
                }

                if (($attendance->overtime_minutes ?? 0) > $overtimeAfter) {
                    $statuses[] = ['label' => 'Overtime', 'color' => 'purple'];
                }

                if ($attendance->state === 'early_checkout') {
                    $statuses[] = ['label' => 'Early Checkout', 'color' => 'orange'];
                }

                if (empty($statuses)) {
                    $statuses[] = ['label' => 'On Time', 'color' => 'green'];
                }

                $break = $attendance->breaks->first();

                $attendance->break_label = $break
                    ? Carbon::parse($break->start_at)->format('H:i')
                        .' - '
                        .Carbon::parse($break->end_at)->format('H:i')
                        .' ('.$break->duration_minutes.'m)'
                    : null;

                $attendance->statuses = $statuses;

                return $attendance;
            });
    }

    /*
    |--------------------------------------------------------------------------
    | ABSENT (BASED ON SCHEDULE)
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function absents()
    {
        $scheduled = EmployeeSchedule::with('user.position')
            ->whereDate('date', $this->date)
            ->when(
                $this->branch_id,
                fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $this->branch_id))
            )
            ->when(
                $this->division_id,
                fn ($q) => $q->whereHas('user.position', fn ($qq) => $qq->where('division_id', $this->division_id))
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
