<?php

use App\Models\Shift;
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

    public ?int $deleteId = null;

    #[Computed]
    public function shifts()
    {
        $this->authorize('viewAny', Shift::class);

        return Shift::latest()->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'cross_midnight' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', Shift::class);

        $this->reset([
            'shiftId',
            'name',
            'start_time',
            'end_time',
            'cross_midnight',
        ]);

        $this->modal('shift-form')->show();
    }

    public function edit(int $id): void
    {
        $shift = Shift::findOrFail($id);

        $this->authorize('update', $shift);

        $this->shiftId = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time;
        $this->end_time = $shift->end_time;
        $this->cross_midnight = $shift->cross_midnight;

        $this->modal('shift-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->shiftId) {

                $shift = Shift::findOrFail($this->shiftId);

                $this->authorize('update', $shift);

                $shift->update($validated);

                $message = 'Shift updated successfully';

            } else {

                $this->authorize('create', Shift::class);

                Shift::create($validated);

                $message = 'Shift created successfully';
            }

            DB::commit();

            $this->modal('shift-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset([
                'shiftId',
                'name',
                'start_time',
                'end_time',
                'cross_midnight',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $shift = Shift::findOrFail($id);

        $this->authorize('delete', $shift);

        $this->deleteId = $id;

        $this->modal('delete-shift')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $shift = Shift::findOrFail($this->deleteId);

            $this->authorize('delete', $shift);

            $shift->delete();

            DB::commit();

            $this->modal('delete-shift')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Shift deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
};
