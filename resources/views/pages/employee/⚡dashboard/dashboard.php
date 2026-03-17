<?php

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?array $todayAttendance = [];

    public ?string $shiftStart = null;

    public ?string $shiftEnd = null;

    public bool $shiftCrossMidnight = false;

    public string $countdown = '-';

    public function mount()
    {
        if (! Auth::user()->can('employee_dashboard.view')) {
            abort(403);
        }

        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $schedule = EmployeeSchedule::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $this->todayAttendance = [
            'checkin' => $attendance?->checkin_at,
            'checkout' => $attendance?->checkout_at,
            'state' => $attendance?->state,
        ];

        if ($schedule) {
            $this->shiftStart = $schedule->shift->start_time;
            $this->shiftEnd = $schedule->shift->end_time;
            $this->shiftCrossMidnight = $schedule->shift->cross_midnight;
        }

        $this->updateCountdown();
    }

    #[Computed()]
    public function leaveBalances()
    {
        return LeaveBalance::where('user_id', Auth::id())
            ->get();
    }

    public function updateCountdown(): void
    {
        if (! $this->shiftStart || ! $this->shiftEnd) {
            $this->countdown = '-';

            return;
        }

        $now = now();

        $start = today()->setTimeFromTimeString($this->shiftStart);
        $end = today()->setTimeFromTimeString($this->shiftEnd);

        if ($this->shiftCrossMidnight || $end->lte($start)) {
            $end->addDay();

            if ($now->lt($start) && $now->hour < 12) {
                $start->subDay();
                $end->subDay();
            }
        }

        $checkin = $this->todayAttendance['checkin'] ?? null;
        $checkout = $this->todayAttendance['checkout'] ?? null;

        if (! empty($checkout)) {
            $this->countdown = 'Shift completed';

            return;
        }

        if (empty($checkin)) {

            if ($now->lt($start)) {

                $diff = $now->diff($start);
                $this->countdown = $diff->format('%Hh %Im to check-in');

            } elseif ($now->between($start, $end)) {

                $diff = $start->diff($now);
                $this->countdown = $diff->format('Late %Hh %Im');

            } else {

                $this->countdown = 'Shift ended';
            }

            return;
        }

        if ($now->lt($end)) {

            $diff = $now->diff($end);
            $this->countdown = $diff->format('%Hh %Im to checkout');

        } else {

            $diff = $end->diff($now);
            $this->countdown = $diff->format('Overtime %Hh %Im');
        }
    }
};
