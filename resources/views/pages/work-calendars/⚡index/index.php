<?php

use App\Models\WorkCalendar;
use App\Services\HolidayImportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 15;

    public ?int $calendarId = null;

    public string $date = '';

    public bool $is_holiday = false;

    public string $description = '';

    public int $year;

    public bool $includeWeekends = true;

    public ?int $deleteId = null;

    public function mount(): void
    {
        $this->year = now()->year;
    }

    #[Computed]
    public function calendars()
    {
        $this->authorize('viewAny', WorkCalendar::class);

        return WorkCalendar::orderBy('date')
            ->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'is_holiday' => ['boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', WorkCalendar::class);

        $this->reset([
            'calendarId',
            'date',
            'is_holiday',
            'description',
        ]);

        $this->modal('calendar-form')->show();
    }

    public function edit(int $id): void
    {
        $calendar = WorkCalendar::findOrFail($id);

        $this->authorize('update', $calendar);

        $this->calendarId = $calendar->id;
        $this->date = $calendar->date->format('Y-m-d');
        $this->is_holiday = $calendar->is_holiday;
        $this->description = $calendar->description ?? '';

        $this->modal('calendar-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->calendarId) {

                $calendar = WorkCalendar::findOrFail($this->calendarId);

                $this->authorize('update', $calendar);

                $calendar->update($validated);

                $message = 'Calendar updated successfully';

            } else {

                $this->authorize('create', WorkCalendar::class);

                WorkCalendar::create($validated);

                $message = 'Calendar created successfully';
            }

            DB::commit();

            $this->modal('calendar-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset(['calendarId', 'date', 'is_holiday', 'description']);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function generateYear(): void
    {
        DB::beginTransaction();

        try {

            $start = Carbon::create($this->year, 1, 1);
            $end = Carbon::create($this->year, 12, 31);

            while ($start <= $end) {

                $isWeekend = $start->isWeekend();

                WorkCalendar::firstOrCreate(
                    ['date' => $start->format('Y-m-d')],
                    [
                        'is_holiday' => $this->includeWeekends ? $isWeekend : false,
                        'description' => $this->includeWeekends && $isWeekend ? 'Weekend' : null,
                    ]
                );

                $start->addDay();
            }

            $service = new HolidayImportService;

            $service->import($this->year);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Work calendar generated for '.$this->year,
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function markWeekendHoliday(): void
    {
        DB::beginTransaction();

        try {

            WorkCalendar::whereRaw('DAYOFWEEK(date) IN (1,7)')
                ->update([
                    'is_holiday' => true,
                    'description' => 'Weekend',
                ]);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'All weekends marked as holiday',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $calendar = WorkCalendar::findOrFail($id);

        $this->authorize('delete', $calendar);

        $this->deleteId = $id;

        $this->modal('delete-calendar')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $calendar = WorkCalendar::findOrFail($this->deleteId);

            $this->authorize('delete', $calendar);

            $calendar->delete();

            DB::commit();

            $this->modal('delete-calendar')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Calendar deleted successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
