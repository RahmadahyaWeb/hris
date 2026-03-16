<?php

use App\Models\WorkCalendar;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();

        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();

        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function getCalendarProperty()
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return WorkCalendar::whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn ($d) => $d->date->format('Y-m-d'));
    }
};
