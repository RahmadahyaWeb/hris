<?php

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $preset = 'this_month';

    public function mount(): void
    {
        if (! Auth::user()->can('employee_attendance-history.view')) {
            abort(403);
        }

        $this->applyPreset($this->preset);
    }

    public function applyPreset(string $preset): void
    {
        $this->preset = $preset;

        match ($preset) {
            'today' => [
                $this->startDate = now()->toDateString(),
                $this->endDate = now()->toDateString(),
            ],
            'this_week' => [
                $this->startDate = now()->startOfWeek()->toDateString(),
                $this->endDate = now()->endOfWeek()->toDateString(),
            ],
            default => [
                $this->startDate = now()->startOfMonth()->toDateString(),
                $this->endDate = now()->endOfMonth()->toDateString(),
            ],
        };
    }

    #[Computed]
    public function attendances()
    {
        return Attendance::where('user_id', Auth::id())
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->latest('date')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function summary()
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->get();

        $present = $attendances->whereNotNull('checkin_at')->count();
        $late = $attendances->where('late_minutes', '>', 0)->count();
        $early = $attendances->where('state', 'early_checkout')->count();
        $overtime = $attendances->where('overtime_minutes', '>', 0)->count();

        $totalWorkMinutes = $attendances->sum('work_minutes');
        $totalOvertimeMinutes = $attendances->sum('overtime_minutes');

        $attendanceRate = $attendances->count() > 0
            ? round(($present / $attendances->count()) * 100, 2)
            : 0;

        return [
            'present' => $present,
            'late' => $late,
            'early' => $early,
            'overtime' => $overtime,
            'work_hours' => round($totalWorkMinutes / 60, 2),
            'overtime_hours' => round($totalOvertimeMinutes / 60, 2),
            'attendance_rate' => $attendanceRate,
        ];
    }
};
