<?php

use App\Models\Attendance;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    #[Computed]
    public function attendances()
    {
        return Attendance::with('user')
            ->whereDate('date', $this->date)
            ->paginate($this->perPage);
    }
};
