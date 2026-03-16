<div class="space-y-6 pb-24">

    {{-- Header --}}
    <div>
        <flux:heading>Attendance</flux:heading>
        <flux:text class="text-sm text-zinc-500">
            Record your daily attendance using your GPS location.
        </flux:text>
    </div>

    {{-- Attendance Status --}}
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
                        $invalidReason = match ($key) {
                            'device' => 'Unauthorized device',
                            'schedule' => 'No schedule today',
                            'holiday' => 'Today is a holiday',
                            'location' => 'Outside branch radius',
                            'duplicate' => 'Attendance already completed',
                            default => 'Validation failed',
                        };

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

        <flux:text class="mt-2 text-xs text-zinc-500">

            @if ($invalidReason)
                Please resolve the issue above before performing attendance.
            @else
                All validation checks passed. You can proceed.
            @endif

        </flux:text>

    </flux:card>

    {{-- Shift Information --}}
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

                <flux:heading size="sm" wire:poll="updateCountdown">
                    {{ $countdown }}
                </flux:heading>

            </div>

        </div>

    </flux:card>

    {{-- Map --}}
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

    {{-- Timeline --}}
    <flux:card>

        <flux:heading size="sm">
            Timeline
        </flux:heading>

        <div class="mt-4 space-y-3">

            @forelse($timeline as $item)
                <div class="flex justify-between text-sm">

                    <span class="text-zinc-500">
                        {{ $item['label'] }}
                    </span>

                    <span class="font-medium">
                        {{ $item['time'] }}
                    </span>

                </div>

            @empty

                <flux:text class="text-sm text-zinc-500">
                    No timeline today
                </flux:text>
            @endforelse

        </div>

    </flux:card>

    {{-- Today Activity --}}
    <flux:card>

        <div class="flex items-center justify-between">

            <flux:heading size="sm">
                Today's Activity
            </flux:heading>

            @if ($attendanceState)
                <flux:badge
                    color="{{ match ($attendanceState) {
                        'late' => 'yellow',
                        'early_checkout' => 'orange',
                        'overtime' => 'purple',
                        default => 'green',
                    } }}">

                    {{ ucfirst(str_replace('_', ' ', $attendanceState)) }}

                </flux:badge>
            @endif

        </div>

        <div class="mt-4 space-y-3 text-sm">

            <div class="flex justify-between">

                <span class="text-zinc-500">
                    Check-in
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkin'] ?? '-' }}
                </span>

            </div>

            <div class="flex justify-between">

                <span class="text-zinc-500">
                    Check-out
                </span>

                <span class="font-medium">
                    {{ $todayAttendance['checkout'] ?? '-' }}
                </span>

            </div>

        </div>

    </flux:card>

    {{-- Action Button --}}
    <div class="sticky bottom-10 bg-white pt-4">

        <flux:button class="w-full h-12 text-base" icon="{{ $checkedIn ? 'arrow-right-circle' : 'map-pin' }}"
            wire:click="attend">

            {{ $checkedIn ? 'Checkout' : 'Check In' }}

        </flux:button>

    </div>

</div>
