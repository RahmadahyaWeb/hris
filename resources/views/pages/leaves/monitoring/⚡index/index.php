<?php

use App\Models\Branch;
use App\Models\Division;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

new class extends Component
{
    public int $month;

    public int $year;

    public ?int $branchId = null;

    public ?int $divisionId = null;

    public array $calendar = [];

    public array $leaveMap = [];

    public array $users = [];

    public array $conflicts = [];

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;

        $this->loadData();
    }

    public function updatedBranchId(): void
    {
        $this->loadData();
    }

    public function updatedDivisionId(): void
    {
        $this->loadData();
    }

    public function prevMonth(): void
    {
        if ($this->month === 1) {
            $this->month = 12;
            $this->year--;
        } else {
            $this->month--;
        }

        $this->loadData();
    }

    public function nextMonth(): void
    {
        if ($this->month === 12) {
            $this->month = 1;
            $this->year++;
        } else {
            $this->month++;
        }

        $this->loadData();
    }

    private function loadData(): void
    {
        $this->loadUsers();
        $this->buildLeaveMap();
        $this->buildCalendar();
        $this->detectConflicts();
    }

    private function loadUsers(): void
    {
        $query = User::with('position.division');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->divisionId) {
            $query->whereHas('position.division', fn ($q) => $q->where('id', $this->divisionId)
            );
        }

        $this->users = $query->orderBy('name')->get()->toArray();
    }

    private function buildLeaveMap(): void
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $leaves = Leave::with(['leaveType', 'user'])
            ->whereIn('user_id', collect($this->users)->pluck('id'))
            ->where(function ($q) use ($start, $end) {

                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });

            })
            ->get();

        $map = [];

        foreach ($leaves as $leave) {

            $date = Carbon::parse($leave->start_date);

            while ($date->lte($leave->end_date)) {

                $map[$leave->user_id][$date->toDateString()][] = $leave;

                $date->addDay();
            }
        }

        $this->leaveMap = $map;
    }

    private function buildCalendar(): void
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $end = $start->copy()->endOfMonth();

        $days = [];
        $date = $start->copy();

        while ($date->lte($end)) {

            $days[] = [
                'date' => $date->toDateString(),
                'day' => $date->day,
                'weekend' => $date->isWeekend(),
            ];

            $date->addDay();
        }

        $this->calendar = $days;
    }

    private function detectConflicts(): void
    {
        $conflicts = [];

        $userIds = collect($this->users)->pluck('id')->toArray();

        foreach ($this->leaveMap as $userId => $days) {

            if (! in_array($userId, $userIds)) {
                continue;
            }

            foreach ($days as $date => $leaves) {

                $conflicts[$date] = ($conflicts[$date] ?? 0) + count($leaves);

            }
        }

        $this->conflicts = $conflicts;
    }

    public function branches()
    {
        return Branch::pluck('name', 'id');
    }

    public function divisions()
    {
        return Division::pluck('name', 'id');
    }
};
