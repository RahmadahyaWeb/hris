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

        $present = 0;
        $late = 0;
        $early = 0;
        $overtime = 0;

        foreach ($attendances as $attendance) {

            if ($attendance->is_present) {
                $present++;
            }

            if ($attendance->is_late) {
                $late++;
            }

            if ($attendance->is_early_checkout) {
                $early++;
            }

            if ($attendance->is_overtime) {
                $overtime++;
            }
        }

        return [
            'present' => $present,
            'late' => $late,
            'early' => $early,
            'overtime' => $overtime,
        ];
    }
};
