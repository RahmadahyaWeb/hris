<?php

use App\Models\Division;
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

    public int $duration_months = 1; // 1, 6, 12

    public ?int $division_id = null;

    public ?int $filter_shift_id = null;

    public ?int $filter_user_id = null;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
    }

    #[Computed]
    public function divisions()
    {
        return Division::pluck('name', 'id');
    }

    #[Computed]
    public function schedules()
    {
        $this->authorize('viewAny', EmployeeSchedule::class);

        return EmployeeSchedule::with(['user.position.division', 'shift'])
            ->when($this->filter_user_id, fn ($q) => $q->where('user_id', $this->filter_user_id)
            )
            ->when($this->filter_shift_id, fn ($q) => $q->where('shift_id', $this->filter_shift_id)
            )
            ->when($this->division_id, fn ($q) => $q->whereHas('user.position', fn ($qq) => $qq->where('division_id', $this->division_id)
            )
            )
            ->join('users', 'users.id', '=', 'employee_schedules.user_id')
            ->join('positions', 'positions.id', '=', 'users.position_id')
            ->join('divisions', 'divisions.id', '=', 'positions.division_id')
            ->orderBy('users.name')
            ->orderBy('divisions.name')
            ->select('employee_schedules.*')
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

            $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();

            $end = match ($this->duration_months) {
                6 => $start->copy()->addMonths(5)->endOfMonth(),
                12 => $start->copy()->addMonths(11)->endOfMonth(),
                default => $start->copy()->endOfMonth(),
            };

            $dates = collect();

            $cursor = $start->copy();

            while ($cursor->lte($end)) {

                $dates->push($cursor->toDateString());

                $cursor->addDay();
            }

            $workCalendars = WorkCalendar::whereBetween('date', [$start, $end])
                ->pluck('is_holiday', 'date');

            $payload = [];

            foreach ($dates as $date) {

                if ($workCalendars[$date] ?? false) {
                    continue;
                }

                $payload[] = [
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'date' => $date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            EmployeeSchedule::upsert(
                $payload,
                ['user_id', 'date'],
                ['shift_id', 'updated_at']
            );

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Schedule generated successfully',
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
