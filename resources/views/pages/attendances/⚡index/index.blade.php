<div class="space-y-6 pb-24">

    <div class="space-y-1">
        <flux:heading>Attendance</flux:heading>
        <flux:text class="text-sm text-zinc-500">
            Record your daily attendance using your current GPS location.
        </flux:text>
    </div>

    <flux:card>

        <div class="grid grid-cols-2 gap-4">

            <div class="p-3 rounded-lg bg-zinc-50">

                <flux:text class="text-xs text-zinc-500">
                    Shift Today
                </flux:text>

                <flux:heading size="sm">
                    {{ $shiftStart ?? '-' }} - {{ $shiftEnd ?? '-' }}
                </flux:heading>

            </div>

            <div class="p-3 rounded-lg bg-zinc-50 text-right">

                <flux:text class="text-xs text-zinc-500">
                    Next Event
                </flux:text>

                <flux:heading size="sm">
                    {{ $countdown }}
                </flux:heading>

            </div>

        </div>

    </flux:card>

    <flux:card>

        <div id="attendance-map" class="h-72 rounded-xl" data-office-lat="{{ $officeLat }}"
            data-office-lng="{{ $officeLng }}" data-office-radius="{{ $officeRadius }}" wire:ignore></div>

        <div class="flex items-center justify-between mt-3 text-sm">

            <span class="text-zinc-500">
                Distance to office
            </span>

            <span class="font-medium">
                {{ number_format($distance, 1) }} m
            </span>

        </div>

        <div class="flex gap-2 mt-3">

            <flux:button size="sm" id="btnUserLocation" class="flex-1">
                My Location
            </flux:button>

            <flux:button size="sm" id="btnOfficeLocation" class="flex-1">
                Office
            </flux:button>

        </div>

    </flux:card>

    <flux:card>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-2">

                <flux:icon name="shield-check" class="w-4 h-4 text-green-500" />

                <span class="text-sm font-medium">
                    Attendance Status
                </span>

            </div>

            @php
                $invalidReason = null;

                foreach ($validation as $key => $value) {
                    if ($value === false) {
                        switch ($key) {
                            case 'device':
                                $invalidReason = 'Unauthorized device';
                                break;

                            case 'schedule':
                                $invalidReason = 'No work schedule today';
                                break;

                            case 'holiday':
                                $invalidReason = 'Today is a holiday';
                                break;

                            case 'location':
                                $invalidReason = 'Outside allowed radius';
                                break;

                            case 'duplicate':
                                $invalidReason = 'Attendance already completed';
                                break;

                            default:
                                $invalidReason = 'Validation failed';
                                break;
                        }

                        break;
                    }
                }
            @endphp

            <div class="self-start sm:self-auto">

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

        </div>

        <flux:text class="mt-2 text-xs text-zinc-500 leading-relaxed">

            @if ($invalidReason)
                Please resolve the issue above before performing attendance.
            @else
                All validation checks passed. You can proceed with attendance.
            @endif

        </flux:text>

    </flux:card>

    <flux:card>

        <flux:heading size="sm">
            Today's Activity
        </flux:heading>

        <div class="mt-4 space-y-3 text-sm">

            <div class="flex items-center justify-between">

                <span class="text-zinc-500">
                    Check-in
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkin'] ?? '-' }}
                </span>

            </div>

            <div class="flex items-center justify-between">

                <span class="text-zinc-500">
                    Check-out
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkout'] ?? '-' }}
                </span>

            </div>

        </div>

    </flux:card>

    <div class="sticky bottom-10 bg-white pt-4">

        <flux:button class="w-full h-12 text-base" icon="{{ $checkedIn ? 'arrow-right-circle' : 'map-pin' }}"
            wire:click="attend">

            {{ $checkedIn ? 'Checkout' : 'Check In' }}

        </flux:button>

    </div>

</div>
