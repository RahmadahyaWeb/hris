<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Exception;
use Illuminate\Support\Facades\Request;

class DeviceValidationService
{
    private const MAX_DEVICES = 3;

    public function validate(User $user, string $deviceUuid): bool
    {
        try {

            $device = UserDevice::where('user_id', $user->id)
                ->where('device_uuid', $deviceUuid)
                ->first();

            if (! $device) {

                $activeDevices = UserDevice::where('user_id', $user->id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->count();

                if ($activeDevices >= self::MAX_DEVICES) {
                    return false;
                }

                UserDevice::create([
                    'user_id' => $user->id,
                    'device_uuid' => $deviceUuid,
                    'user_agent' => Request::userAgent(),
                    'ip_address' => Request::ip(),
                    'requested_at' => now(),
                    'status' => 'pending',
                ]);

                return false;
            }

            if ($device->status !== 'approved') {
                return false;
            }

            $device->update([
                'last_login_at' => now(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function approve(UserDevice $device, ?string $deviceName = null): UserDevice
    {
        try {

            $device->update([
                'device_name' => $deviceName,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return $device;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function reject(UserDevice $device): void
    {
        try {

            $device->update([
                'status' => 'rejected',
            ]);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
