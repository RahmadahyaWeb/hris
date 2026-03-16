<?php

use App\Models\Branch;
use App\Models\Division;
use App\Services\AttendanceSummaryService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $start_date;

    public string $end_date;

    public ?int $branch_id = null;

    public ?int $division_id = null;

    public function mount(): void
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
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

    #[Computed]
    public function report()
    {
        $service = new AttendanceSummaryService;

        return $service->period(
            $this->start_date,
            $this->end_date,
            $this->branch_id,
            $this->division_id
        );
    }
};
