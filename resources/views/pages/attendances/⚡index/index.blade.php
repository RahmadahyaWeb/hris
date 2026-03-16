<div class="space-y-6">

    <div>
        <flux:heading>Attendance</flux:heading>
        <flux:text class="mt-1 text-sm">
            Record your attendance using GPS location.
        </flux:text>
    </div>

    <flux:card>

        <div class="flex items-center justify-between">

            <div>

                <flux:text class="text-xs text-zinc-500">
                    Shift
                </flux:text>

                <flux:heading size="sm">
                    {{ $shiftStart ?? '-' }} - {{ $shiftEnd ?? '-' }}
                </flux:heading>

            </div>

            <div>

                <flux:text class="text-xs text-zinc-500">
                    Countdown
                </flux:text>

                <flux:heading size="sm">
                    {{ $countdown }}
                </flux:heading>

            </div>

        </div>

    </flux:card>

    <flux:card>

        <div id="attendance-map" class="h-80 rounded-xl" data-office-lat="{{ $officeLat }}"
            data-office-lng="{{ $officeLng }}" data-office-radius="{{ $officeRadius }}" wire:ignore></div>

        <div class="flex justify-between mt-3">

            <flux:button size="sm" id="btnUserLocation">
                My Location
            </flux:button>

            <flux:button size="sm" id="btnOfficeLocation">
                Office
            </flux:button>

        </div>

        <div class="mt-2 text-sm text-zinc-500">

            Distance to office: {{ number_format($distance, 1) }} m

        </div>

    </flux:card>

    <flux:card>

        <div class="flex items-center justify-between">

            <div class="flex items-center gap-2">

                <flux:icon name="shield-check" class="w-4 h-4 text-green-500" />

                <span class="text-sm">
                    Attendance validation
                </span>

            </div>

            @php
                $invalidReason = null;

                foreach ($validation as $key => $value) {
                    if ($value === false) {
                        switch ($key) {
                            case 'device':
                                $invalidReason = 'Device not authorized';
                                break;

                            case 'schedule':
                                $invalidReason = 'No schedule today';
                                break;

                            case 'holiday':
                                $invalidReason = 'Today is a holiday';
                                break;

                            case 'location':
                                $invalidReason = 'Outside branch radius';
                                break;

                            case 'duplicate':
                                $invalidReason = 'Attendance already recorded';
                                break;

                            default:
                                $invalidReason = 'Validation failed';
                                break;
                        }

                        break;
                    }
                }
            @endphp

            @if ($invalidReason)
                <flux:badge color="red">
                    {{ $invalidReason }}
                </flux:badge>
            @else
                <flux:badge color="green">
                    Ready
                </flux:badge>
            @endif

        </div>

    </flux:card>

    <flux:card>

        <flux:heading>
            Today's Activity
        </flux:heading>

        <div class="mt-3 space-y-2">

            <div class="flex justify-between">

                <span class="text-sm text-zinc-500">
                    Check-in
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkin'] ?? '-' }}
                </span>

            </div>

            <div class="flex justify-between">

                <span class="text-sm text-zinc-500">
                    Check-out
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkout'] ?? '-' }}
                </span>

            </div>

        </div>

    </flux:card>

    <div class="fixed bottom-6 left-0 right-0 flex justify-center">

        <flux:button class="w-64 shadow-xl" icon="{{ $checkedIn ? 'arrow-right-circle' : 'map-pin' }}"
            wire:click="attend">

            {{ $checkedIn ? 'Checkout' : 'Check In' }}

        </flux:button>

    </div>
</div>
