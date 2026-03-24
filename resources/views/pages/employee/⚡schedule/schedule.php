<?php

use App\Services\EmployeeScheduleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public array $week = [];

    public array $today = [];

    public function mount(): void
    {
        $service = new EmployeeScheduleService;

        $this->week = $service->getWeek(Auth::id());

        $todayKey = now()->toDateString();

        foreach ($this->week as $item) {
            if ($item['date'] === $todayKey) {
                $this->today = $item;
                break;
            }
        }
    }
};
