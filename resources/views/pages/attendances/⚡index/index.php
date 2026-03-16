<?php

use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Services\AttendanceService;
use App\Services\AttendanceValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public ?float $latitude = null;

    public ?float $longitude = null;

    public ?string $device_uuid = null;

    public bool $checkedIn = false;

    public array $validation = [];

    public ?string $shiftStart = null;

    public ?string $shiftEnd = null;

    public ?float $officeLat = null;

    public ?float $officeLng = null;

    public ?int $officeRadius = null;

    public float $distance = 0;

    public array $todayAttendance = [];

    public string $countdown = '';

    public function mount(): void
    {
        $user = Auth::user();

        $branch = $user->branch;

        $this->officeLat = $branch->latitude;
        $this->officeLng = $branch->longitude;
        $this->officeRadius = $branch->radius;

        $schedule = EmployeeSchedule::with('shift')->where('user_id', $user->id)->whereDate('date', today())->first();

        if ($schedule) {
            $this->shiftStart = $schedule->shift->start_time;
            $this->shiftEnd = $schedule->shift->end_time;
        }

        $attendance = Attendance::where([
            'user_id' => $user->id,
            'date' => today(),
        ])->first();

        if ($attendance) {
            $this->todayAttendance = [
                'checkin' => $attendance->checkin_at,
                'checkout' => $attendance->checkout_at,
            ];

            $this->checkedIn = $attendance->checkin_at && ! $attendance->checkout_at;
        }
    }

    public function setDevice(string $uuid): void
    {
        $this->device_uuid = $uuid;

        $mode = $this->checkedIn ? 'checkout' : 'checkin';

        $validator = new AttendanceValidationService;

        $this->validation = $validator->validate(Auth::user(), $this->device_uuid, $this->latitude ?? 0, $this->longitude ?? 0, $mode);
    }

    public function attend(): void
    {
        DB::beginTransaction();

        try {
            $mode = $this->checkedIn ? 'checkout' : 'checkin';

            $validator = new AttendanceValidationService;

            $this->validation = $validator->validate(Auth::user(), $this->device_uuid, $this->latitude, $this->longitude, $mode);

            foreach ($this->validation as $key => $value) {
                if ($value === false) {
                    switch ($key) {
                        case 'device':
                            throw new Exception('This device is not authorized.');
                        case 'schedule':
                            throw new Exception('You do not have a work schedule today.');
                        case 'holiday':
                            throw new Exception('Today is marked as a holiday.');
                        case 'location':
                            throw new Exception('You are outside the allowed branch radius.');
                        case 'duplicate':
                            if ($mode === 'checkin') {
                                throw new Exception('You have already checked in today.');
                            }

                            if ($mode === 'checkout') {
                                throw new Exception('Checkout is not allowed. Check-in record not found.');
                            }

                            throw new Exception('Attendance validation failed.');
                        default:
                            throw new Exception('Attendance validation failed.');
                    }
                }
            }

            $service = new AttendanceService;

            if ($mode === 'checkin') {
                $service->checkin(Auth::user());

                $this->checkedIn = true;

                $message = 'Check-in successful';
            } else {
                $service->checkout(Auth::user());

                $this->checkedIn = false;

                $message = 'Checkout successful';
            }

            DB::commit();

            $this->dispatch('alert', [
                'title' => 'Success',
                'message' => $message,
                'variant' => 'success',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            $this->dispatch('alert', [
                'title' => 'Attendance Failed',
                'message' => $e->getMessage(),
                'variant' => 'danger',
            ]);
        }
    }
};
