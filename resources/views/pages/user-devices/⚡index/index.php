<?php

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $maxDevices = 3;

    #[Computed]
    public function users()
    {
        $this->authorize('viewAny', UserDevice::class);

        return User::with(['devices' => function ($q) {
            $q->latest();
        }])
            ->withCount('devices')
            ->paginate();
    }

    public function approve(int $id): void
    {
        DB::beginTransaction();

        try {

            $device = UserDevice::findOrFail($id);

            $this->authorize('update', $device);

            $device->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Device approved successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function block(int $id): void
    {
        DB::beginTransaction();

        try {

            $device = UserDevice::findOrFail($id);

            $this->authorize('update', $device);

            $device->update([
                'status' => 'blocked',
            ]);

            DB::table('sessions')
                ->where('user_id', $device->user_id)
                ->delete();

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Device blocked and user logged out',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function revoke(int $id): void
    {
        DB::beginTransaction();

        try {

            $device = UserDevice::findOrFail($id);

            $this->authorize('delete', $device);

            $device->delete();

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => 'Device revoked successfully',
                'variant' => 'success',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }
};
