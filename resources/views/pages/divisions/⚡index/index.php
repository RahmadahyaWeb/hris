<?php

use App\Models\Division;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $divisionId = null;

    public string $name = '';

    public ?int $deleteId = null;

    #[Computed]
    public function divisions()
    {
        $this->authorize('viewAny', Division::class);

        return Division::withCount('positions')
            ->latest()
            ->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', Division::class);

        $this->reset(['divisionId', 'name']);

        $this->modal('division-form')->show();
    }

    public function edit(int $id): void
    {
        $division = Division::findOrFail($id);

        $this->authorize('update', $division);

        $this->divisionId = $division->id;
        $this->name = $division->name;

        $this->modal('division-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->divisionId) {

                $division = Division::findOrFail($this->divisionId);

                $this->authorize('update', $division);

                $division->update($validated);

                $message = 'Division updated successfully';

            } else {

                $this->authorize('create', Division::class);

                Division::create($validated);

                $message = 'Division created successfully';
            }

            DB::commit();

            $this->modal('division-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset(['divisionId', 'name']);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $division = Division::findOrFail($id);

        $this->authorize('delete', $division);

        $this->deleteId = $id;

        $this->modal('delete-division')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $division = Division::findOrFail($this->deleteId);

            $this->authorize('delete', $division);

            $division->delete();

            DB::commit();

            $this->modal('delete-division')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Division deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
