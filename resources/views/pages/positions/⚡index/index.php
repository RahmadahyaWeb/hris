<?php

use App\Models\Division;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $positionId = null;

    public string $title = '';

    public ?int $division_id = null;

    public ?int $deleteId = null;

    public ?int $parentTargetId = null;

    #[Computed]
    public function positions()
    {
        $this->authorize('viewAny', Position::class);

        return Position::with('division')
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function divisions()
    {
        return Division::pluck('name', 'id');
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
        ];
    }

    public function setAsParent(int $id): void
    {
        DB::beginTransaction();

        try {

            $position = Position::findOrFail($id);

            $this->authorize('update', $position);

            /*
            |------------------------------------------------------------
            | VALIDATION: ONLY SAME DIVISION
            |------------------------------------------------------------
            */

            // pastikan hanya 1 parent per division
            Position::where('division_id', $position->division_id)
                ->whereNull('parent_id')
                ->where('id', '!=', $position->id)
                ->update([
                    'parent_id' => $position->id, // turunkan parent lama jadi child
                ]);

            /*
            |------------------------------------------------------------
            | SET CURRENT AS PARENT
            |------------------------------------------------------------
            */

            $position->update([
                'parent_id' => null,
            ]);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Parent updated within division successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function create(): void
    {
        $this->authorize('create', Position::class);

        $this->reset(['positionId', 'title', 'division_id']);

        $this->modal('position-form')->show();
    }

    public function edit(int $id): void
    {
        $position = Position::findOrFail($id);

        $this->authorize('update', $position);

        $this->positionId = $position->id;
        $this->title = $position->title;
        $this->division_id = $position->division_id;

        $this->modal('position-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->positionId) {

                $position = Position::findOrFail($this->positionId);

                $this->authorize('update', $position);

                $position->update($validated);

                $message = 'Position updated successfully';

            } else {

                $this->authorize('create', Position::class);

                Position::create($validated);

                $message = 'Position created successfully';
            }

            DB::commit();

            $this->modal('position-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset(['positionId', 'title', 'division_id']);

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $position = Position::findOrFail($id);

        $this->authorize('delete', $position);

        $this->deleteId = $id;

        $this->modal('delete-position')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $position = Position::findOrFail($this->deleteId);

            $this->authorize('delete', $position);

            $position->delete();

            DB::commit();

            $this->modal('delete-position')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Position deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
};
