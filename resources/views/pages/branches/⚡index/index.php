<?php

use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $branchId = null;

    public string $name = '';

    public string $latitude = '';

    public string $longitude = '';

    public int $radius = 100;

    public ?int $deleteId = null;

    #[Computed]
    public function branches()
    {
        $this->authorize('viewAny', Branch::class);

        return Branch::latest()->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius' => ['required', 'integer', 'min:1'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', Branch::class);

        $this->reset(['branchId', 'name', 'latitude', 'longitude', 'radius']);

        $this->modal('branch-form')->show();
    }

    public function edit(int $id): void
    {
        $branch = Branch::findOrFail($id);

        $this->authorize('update', $branch);

        $this->branchId = $branch->id;
        $this->name = $branch->name;
        $this->latitude = $branch->latitude;
        $this->longitude = $branch->longitude;
        $this->radius = $branch->radius;

        $this->modal('branch-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->branchId) {

                $branch = Branch::findOrFail($this->branchId);

                $this->authorize('update', $branch);

                $branch->update($validated);

                $message = 'Branch updated successfully';

            } else {

                $this->authorize('create', Branch::class);

                Branch::create($validated);

                $message = 'Branch created successfully';
            }

            DB::commit();

            $this->modal('branch-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset(['branchId', 'name', 'latitude', 'longitude', 'radius']);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $branch = Branch::findOrFail($id);

        $this->authorize('delete', $branch);

        $this->deleteId = $id;

        $this->modal('delete-branch')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $branch = Branch::findOrFail($this->deleteId);

            $this->authorize('delete', $branch);

            $branch->delete();

            DB::commit();

            $this->modal('delete-branch')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Branch deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
