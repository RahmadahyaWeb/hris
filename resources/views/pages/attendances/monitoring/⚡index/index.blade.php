<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <flux:heading size="lg">Attendance Monitoring</flux:heading>
            <flux:text class=" text-zinc-500">
                Daily attendance overview based on schedule.
            </flux:text>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">

            <flux:input type="date" wire:model.live="date" class="w-full sm:w-auto" />

            <flux:select wire:model.live="branch_id" class="w-full sm:w-48">
                <option value="">All Branches</option>

                @foreach ($this->branches as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>

        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        <flux:card class="p-3">
            <flux:text class="text-xs text-zinc-500">Present</flux:text>
            <flux:heading size="lg">{{ $this->summary['present'] }}</flux:heading>
        </flux:card>

        <flux:card class="p-3">
            <flux:text class="text-xs text-zinc-500">Late</flux:text>
            <flux:heading size="lg">{{ $this->summary['late'] }}</flux:heading>
        </flux:card>

        <flux:card class="p-3">
            <flux:text class="text-xs text-zinc-500">Overtime</flux:text>
            <flux:heading size="lg">{{ $this->summary['overtime'] }}</flux:heading>
        </flux:card>

        <flux:card class="p-3">
            <flux:text class="text-xs text-zinc-500">Absent</flux:text>
            <flux:heading size="lg">{{ $this->summary['absent'] }}</flux:heading>
        </flux:card>

    </div>

    {{-- MAIN --}}
    <div class="flex flex-col gap-6 lg:grid lg:grid-cols-3">

        {{-- TABLE --}}
        <flux:card class="lg:col-span-2 overflow-x-auto">

            <flux:heading size="lg">
                Attendance Activity
            </flux:heading>

            <div class="mt-4 min-w-[600px]">

                <flux:table :paginate="$this->attendances">

                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Check In</flux:table.column>
                        <flux:table.column>Check Out</flux:table.column>
                        <flux:table.column>Work</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>

                        @forelse ($this->attendances as $attendance)

                            @php
                                $statuses = [];

                                if (($attendance->late_minutes ?? 0) > 0) {
                                    $statuses[] = 'Late';
                                }

                                if (($attendance->overtime_minutes ?? 0) > 0) {
                                    $statuses[] = 'Overtime';
                                }

                                if ($attendance->state === 'early_checkout') {
                                    $statuses[] = 'Early Checkout';
                                }

                                if (empty($statuses)) {
                                    $statuses[] = 'On Time';
                                }
                            @endphp

                            <flux:table.row>

                                <flux:table.cell>

                                    <div class="flex flex-col">

                                        <span class="font-medium">
                                            {{ $attendance->user->name }}
                                        </span>

                                        <span class="text-xs text-zinc-400">
                                            {{ $attendance->user->position->title ?? '-' }}
                                        </span>

                                    </div>

                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $attendance->checkin_at?->format('H:i') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $attendance->checkout_at?->format('H:i') ?? '-' }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ round(($attendance->work_minutes ?? 0) / 60, 2) }} h
                                </flux:table.cell>

                                <flux:table.cell>

                                    <div class="flex flex-wrap gap-1">

                                        @foreach ($statuses as $status)
                                            <flux:badge
                                                color="{{ match ($status) {
                                                    'Late' => 'yellow',
                                                    'Overtime' => 'purple',
                                                    'Early Checkout' => 'orange',
                                                    default => 'green',
                                                } }}">

                                                {{ $status }}

                                            </flux:badge>
                                        @endforeach

                                    </div>

                                </flux:table.cell>

                            </flux:table.row>

                        @empty

                            <flux:table.row>

                                <flux:table.cell colspan="5">

                                    <div class="flex flex-col items-center justify-center py-10">

                                        <flux:heading size="sm">
                                            No Attendance Data
                                        </flux:heading>

                                        <flux:text class="mt-1 text-sm text-zinc-500">
                                            No attendance recorded for this day.
                                        </flux:text>

                                    </div>

                                </flux:table.cell>

                            </flux:table.row>

                        @endforelse

                    </flux:table.rows>

                </flux:table>

            </div>

        </flux:card>

        {{-- SIDE --}}
        <div class="space-y-6">

            <flux:card>

                <flux:heading size="lg">
                    Late Leaderboard
                </flux:heading>

                <div class="mt-4 space-y-2">

                    @forelse ($this->lateLeaderboard as $item)
                        <div class="flex items-center justify-between text-sm">

                            <span class="truncate">
                                {{ $item->user->name }}
                            </span>

                            <flux:badge color="yellow">
                                {{ $item->late_minutes }} min
                            </flux:badge>

                        </div>

                    @empty

                        <flux:text class=" text-zinc-500">
                            No late employees.
                        </flux:text>
                    @endforelse

                </div>

            </flux:card>

            <flux:card>

                <flux:heading size="lg">
                    Absent Employees
                </flux:heading>

                <div class="mt-4 space-y-2">

                    @forelse ($this->absents as $item)
                        <div class="flex items-center justify-between text-sm">

                            <span class="truncate">
                                {{ $item->user->name }}
                            </span>

                            <flux:badge color="red">
                                Absent
                            </flux:badge>

                        </div>

                    @empty

                        <flux:text class=" text-zinc-500">
                            No absent employees today.
                        </flux:text>
                    @endforelse

                </div>

            </flux:card>

        </div>

    </div>

</div>
