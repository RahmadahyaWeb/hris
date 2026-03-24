<div class="space-y-6">

    <!-- HEADER -->
    <div>
        <flux:heading size="lg">My Attendance</flux:heading>
        <flux:text class=" text-zinc-500">
            View your attendance history and performance.
        </flux:text>
    </div>

    <!-- FILTER -->
    <flux:card class="p-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex gap-2">

                <flux:button size="sm" wire:click="applyPreset('today')"
                    variant="{{ $preset === 'today' ? 'primary' : 'outline' }}">
                    Today
                </flux:button>

                <flux:button size="sm" wire:click="applyPreset('this_week')"
                    variant="{{ $preset === 'this_week' ? 'primary' : 'outline' }}">
                    Week
                </flux:button>

                <flux:button size="sm" wire:click="applyPreset('this_month')"
                    variant="{{ $preset === 'this_month' ? 'primary' : 'outline' }}">
                    Month
                </flux:button>

            </div>

            <div class="flex gap-2">

                <flux:input type="date" wire:model.live="startDate" />
                <flux:input type="date" wire:model.live="endDate" />

            </div>

        </div>

    </flux:card>

    {{-- TAMBAHAN SECTION DI ATAS SUMMARY (tetap mobile-first) --}}

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Present</div>
            <div class="text-lg font-semibold">{{ $this->summary['present'] }}</div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Late</div>
            <div class="text-lg font-semibold text-yellow-500">
                {{ $this->summary['late'] }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Overtime</div>
            <div class="text-lg font-semibold text-blue-500">
                {{ $this->summary['overtime'] }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Rate</div>
            <div class="text-lg font-semibold">
                {{ $this->summary['attendance_rate'] }}%
            </div>
        </flux:card>

    </div>

    {{-- TAMBAHAN WORK SUMMARY --}}

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

        <flux:card class="p-4">

            <flux:text class="text-xs text-zinc-500">
                Total Work Hours
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['work_hours'] }} h
            </flux:heading>

        </flux:card>

        <flux:card class="p-4">

            <flux:text class="text-xs text-zinc-500">
                Total Overtime
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['overtime_hours'] }} h
            </flux:heading>

        </flux:card>

    </div>

    <!-- SUMMARY -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Present</div>
            <div class="text-lg font-semibold">{{ $this->summary['present'] }}</div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Late</div>
            <div class="text-lg font-semibold text-yellow-500">
                {{ $this->summary['late'] }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Early</div>
            <div class="text-lg font-semibold text-red-500">
                {{ $this->summary['early'] }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Overtime</div>
            <div class="text-lg font-semibold text-blue-500">
                {{ $this->summary['overtime'] }}
            </div>
        </flux:card>

    </div>

    <!-- LIST -->
    <div class="space-y-3">

        @forelse($this->attendances as $attendance)
            <flux:card class="p-4">

                <div class="flex items-center justify-between">

                    <div>
                        <div class="text-sm font-medium">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                        </div>

                        <div class="text-xs text-zinc-500">
                            {{ $attendance->checkin_at ? \Carbon\Carbon::parse($attendance->checkin_at)->format('H:i') : '--' }}
                            -
                            {{ $attendance->checkout_at ? \Carbon\Carbon::parse($attendance->checkout_at)->format('H:i') : '--' }}
                        </div>
                    </div>

                    <flux:badge
                        color="{{ match ($attendance->state) {
                            'late' => 'yellow',
                            'early_checkout' => 'red',
                            'overtime' => 'blue',
                            default => 'green',
                        } }}">

                        {{ ucwords(str_replace('_', ' ', $attendance->state ?? 'unknown')) }}

                    </flux:badge>
                </div>

                <div class="mt-3 grid grid-cols-3 text-xs text-zinc-500">

                    <div>
                        Work<br>
                        <span class="text-sm font-medium text-zinc-800">
                            {{ $attendance->work_minutes }} min
                        </span>
                    </div>

                    <div>
                        Late<br>
                        <span class="text-sm font-medium text-yellow-500">
                            {{ $attendance->late_minutes }} min
                        </span>
                    </div>

                    <div>
                        OT<br>
                        <span class="text-sm font-medium text-blue-500">
                            {{ $attendance->overtime_minutes }} min
                        </span>
                    </div>

                </div>

            </flux:card>

        @empty

            <flux:card class="p-6 text-center">
                <flux:text>No attendance data found</flux:text>
            </flux:card>
        @endforelse

    </div>

    <!-- PAGINATION -->
    <div>
        {{ $this->attendances->links() }}
    </div>

</div>
