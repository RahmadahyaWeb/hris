<?php

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $scheduleId = null;

    public ?int $user_id = null;

    public ?int $shift_id = null;

    public string $date = '';

    public int $year;

    public int $month;

    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
    }

    #[Computed]
    public function schedules()
    {
        $this->authorize('viewAny', EmployeeSchedule::class);

        return EmployeeSchedule::with(['user', 'shift'])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function users()
    {
        return User::pluck('name', 'id');
    }

    #[Computed]
    public function shifts()
    {
        return Shift::pluck('name', 'id');
    }

    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'date' => ['required', 'date'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', EmployeeSchedule::class);

        $this->reset([
            'scheduleId',
            'user_id',
            'shift_id',
            'date',
        ]);

        $this->modal('schedule-form')->show();
    }

    public function edit(int $id): void
    {
        $schedule = EmployeeSchedule::findOrFail($id);

        $this->authorize('update', $schedule);

        $this->scheduleId = $schedule->id;
        $this->user_id = $schedule->user_id;
        $this->shift_id = $schedule->shift_id;
        $this->date = $schedule->date->format('Y-m-d');

        $this->modal('schedule-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->scheduleId) {

                $schedule = EmployeeSchedule::findOrFail($this->scheduleId);

                $this->authorize('update', $schedule);

                $schedule->update($validated);

                $message = 'Schedule updated successfully';

            } else {

                $this->authorize('create', EmployeeSchedule::class);

                EmployeeSchedule::create($validated);

                $message = 'Schedule created successfully';
            }

            DB::commit();

            $this->modal('schedule-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset([
                'scheduleId',
                'user_id',
                'shift_id',
                'date',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function generateUserSchedule(): void
    {
        $this->authorize('create', EmployeeSchedule::class);

        DB::beginTransaction();

        try {

            $user = User::findOrFail($this->user_id);
            $shift = Shift::findOrFail($this->shift_id);

            $start = Carbon::create($this->year, $this->month, 1);
            $end = $start->copy()->endOfMonth();

            $date = $start->copy();

            while ($date <= $end) {

                $calendar = WorkCalendar::whereDate('date', $date)->first();

                if ($calendar && $calendar->is_holiday) {

                    $date->addDay();

                    continue;
                }

                EmployeeSchedule::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'shift_id' => $shift->id,
                    ]
                );

                $date->addDay();
            }

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'User schedule generated successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $schedule = EmployeeSchedule::findOrFail($id);

        $this->authorize('delete', $schedule);

        $this->deleteId = $id;

        $this->modal('delete-schedule')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $schedule = EmployeeSchedule::findOrFail($this->deleteId);

            $this->authorize('delete', $schedule);

            $schedule->delete();

            DB::commit();

            $this->modal('delete-schedule')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Schedule deleted successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
