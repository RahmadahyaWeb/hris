<?php

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $shiftId = null;

    public string $name = '';

    public string $start_time = '';

    public string $end_time = '';

    public bool $cross_midnight = false;

    public array $breaks = [];

    public ?int $deleteId = null;

    #[Computed]
    public function shifts()
    {
        return Shift::with('breaks')
            ->latest()
            ->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'cross_midnight' => ['boolean'],
            'breaks.*.start_time' => ['required'],
            'breaks.*.end_time' => ['required'],
        ];
    }

    public function addBreak(): void
    {
        $this->breaks[] = [
            'start_time' => '',
            'end_time' => '',
        ];
    }

    public function removeBreak($index): void
    {
        unset($this->breaks[$index]);
        $this->breaks = array_values($this->breaks);
    }

    public function create(): void
    {
        $this->reset([
            'shiftId',
            'name',
            'start_time',
            'end_time',
            'cross_midnight',
            'breaks',
        ]);

        $this->modal('shift-form')->show();
    }

    public function edit(int $id): void
    {
        $shift = Shift::with('breaks')->findOrFail($id);

        $this->shiftId = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time;
        $this->end_time = $shift->end_time;
        $this->cross_midnight = $shift->cross_midnight;

        $this->breaks = $shift->breaks->map(fn ($b) => [
            'start_time' => $b->start_time,
            'end_time' => $b->end_time,
        ])->toArray();

        $this->modal('shift-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->shiftId) {

                $shift = Shift::findOrFail($this->shiftId);

                $shift->update($validated);

            } else {

                $shift = Shift::create($validated);
            }

            // RESET BREAK
            $shift->breaks()->delete();

            foreach ($this->breaks as $break) {

                $start = Carbon::parse($break['start_time']);
                $end = Carbon::parse($break['end_time']);

                $shift->breaks()->create([
                    'start_time' => $break['start_time'],
                    'end_time' => $break['end_time'],
                    'duration_minutes' => $start->diffInMinutes($end),
                ]);
            }

            DB::commit();

            $this->modal('shift-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Shift saved',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
