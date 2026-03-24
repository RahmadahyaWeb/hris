<?php

use App\Services\EmployeeWorkCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public int $year;

    public int $month;

    public array $calendar = [];

    public array $selectedDay = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->load();
    }

    public function prev(): void
    {
        $date = Carbon::create($this->year, $this->month)->subMonth();

        $this->year = $date->year;
        $this->month = $date->month;

        $this->load();
    }

    public function next(): void
    {
        $date = Carbon::create($this->year, $this->month)->addMonth();

        $this->year = $date->year;
        $this->month = $date->month;

        $this->load();
    }

    public function load(): void
    {
        $service = new EmployeeWorkCalendarService;

        $this->calendar = $service->generate(
            Auth::id(),
            $this->year,
            $this->month
        );
    }

    public function showDetailModal(array $day): void
    {
        $this->selectedDay = $day;

        $this->modal('calendar-detail')->show();
    }
};
