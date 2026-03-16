<div class="space-y-6">

    <div>
        <flux:heading>Attendance Monitoring</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-500">
            Real-time overview of employee attendance activity.
        </flux:text>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <flux:input type="date" wire:model.live="date" />

        <div class="w-full sm:w-60">
            <flux:select wire:model.live="branch_id">
                <option value="">All Branches</option>

                @foreach ($this->branches as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach
            </flux:select>
        </div>

    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

        <flux:card>
            <flux:text class="text-xs text-zinc-500">
                Present
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['present'] }}
            </flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">
                Late
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['late'] }}
            </flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">
                Overtime
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['overtime'] }}
            </flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">
                Absent
            </flux:text>

            <flux:heading size="lg">
                {{ $this->summary['absent'] }}
            </flux:heading>
        </flux:card>

    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        <flux:card class="lg:col-span-2">

            <flux:heading size="lg">
                Attendance Activity
            </flux:heading>

            <flux:table :paginate="$this->attendances">

                <flux:table.columns>
                    <flux:table.column>User</flux:table.column>
                    <flux:table.column>Division</flux:table.column>
                    <flux:table.column>Check In</flux:table.column>
                    <flux:table.column>Check Out</flux:table.column>
                    <flux:table.column>Work</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>

                    @forelse ($this->attendances as $attendance)
                        <flux:table.row>

                            <flux:table.cell>

                                <div class="flex flex-col">

                                    <span>
                                        {{ $attendance->user->name }}
                                    </span>

                                    <span class="text-xs text-zinc-500">
                                        {{ $attendance->user->position->title ?? '-' }}
                                    </span>

                                </div>

                            </flux:table.cell>

                            <flux:table.cell>

                                {{ $attendance->user->position->division->name ?? '-' }}

                            </flux:table.cell>

                            <flux:table.cell>

                                {{ $attendance->checkin_at?->format('H:i') }}

                            </flux:table.cell>

                            <flux:table.cell>

                                {{ $attendance->checkout_at?->format('H:i') ?? '-' }}

                            </flux:table.cell>

                            <flux:table.cell>

                                {{ $attendance->work_minutes }} min

                            </flux:table.cell>

                            <flux:table.cell>

                                @php
                                    $color = match ($attendance->state) {
                                        'late' => 'yellow',
                                        'overtime' => 'purple',
                                        'early_checkout' => 'orange',
                                        default => 'green',
                                    };
                                @endphp

                                <flux:badge color="{{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->state ?? 'on_time')) }}
                                </flux:badge>

                            </flux:table.cell>

                        </flux:table.row>

                    @empty

                        <flux:table.row>

                            <flux:table.cell colspan="6">

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

        </flux:card>

        <div class="space-y-6">

            <flux:card>

                <flux:heading size="lg">
                    Late Leaderboard
                </flux:heading>

                <div class="mt-4 space-y-2">

                    @forelse ($this->lateLeaderboard as $item)
                        <div class="flex justify-between text-sm">

                            <span>
                                {{ $item->user->name }}
                            </span>

                            <flux:badge color="yellow">
                                {{ $item->late_minutes }} min
                            </flux:badge>

                        </div>

                    @empty

                        <flux:text class="text-sm text-zinc-500">
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
                        <div class="flex justify-between text-sm">

                            <span>
                                {{ $item->user->name }}
                            </span>

                            <flux:badge color="red">
                                Absent
                            </flux:badge>

                        </div>

                    @empty

                        <flux:text class="text-sm text-zinc-500">
                            No absent employees today.
                        </flux:text>
                    @endforelse

                </div>

            </flux:card>

        </div>

    </div>

</div>
