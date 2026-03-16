<?php

use App\Models\AttendanceRule;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public ?int $ruleId = null;

    public int $late_tolerance_minutes = 0;

    public int $early_checkout_tolerance = 0;

    public int $overtime_after_minutes = 0;

    public ?int $deleteId = null;

    #[Computed]
    public function attendanceRules()
    {
        $this->authorize('viewAny', AttendanceRule::class);

        return AttendanceRule::latest()->paginate($this->perPage);
    }

    protected function rules(): array
    {
        return [
            'late_tolerance_minutes' => ['required', 'integer', 'min:0'],
            'early_checkout_tolerance' => ['required', 'integer', 'min:0'],
            'overtime_after_minutes' => ['required', 'integer', 'min:0'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', AttendanceRule::class);

        $this->reset([
            'ruleId',
            'late_tolerance_minutes',
            'early_checkout_tolerance',
            'overtime_after_minutes',
        ]);

        $this->modal('rule-form')->show();
    }

    public function edit(int $id): void
    {
        $rule = AttendanceRule::findOrFail($id);

        $this->authorize('update', $rule);

        $this->ruleId = $rule->id;
        $this->late_tolerance_minutes = $rule->late_tolerance_minutes;
        $this->early_checkout_tolerance = $rule->early_checkout_tolerance;
        $this->overtime_after_minutes = $rule->overtime_after_minutes;

        $this->modal('rule-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {

            if ($this->ruleId) {

                $rule = AttendanceRule::findOrFail($this->ruleId);

                $this->authorize('update', $rule);

                $rule->update($validated);

                $message = 'Attendance rule updated successfully';

            } else {

                $this->authorize('create', AttendanceRule::class);

                AttendanceRule::create($validated);

                $message = 'Attendance rule created successfully';
            }

            DB::commit();

            $this->modal('rule-form')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);

            $this->reset([
                'ruleId',
                'late_tolerance_minutes',
                'early_checkout_tolerance',
                'overtime_after_minutes',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $rule = AttendanceRule::findOrFail($id);

        $this->authorize('delete', $rule);

        $this->deleteId = $id;

        $this->modal('delete-rule')->show();
    }

    public function destroy(): void
    {
        DB::beginTransaction();

        try {

            $rule = AttendanceRule::findOrFail($this->deleteId);

            $this->authorize('delete', $rule);

            $rule->delete();

            DB::commit();

            $this->modal('delete-rule')->close();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Attendance rule deleted successfully',
                'variant' => 'success',
            ]);

            $this->deleteId = null;

        } catch (Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
};
