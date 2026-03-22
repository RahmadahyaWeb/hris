<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <flux:heading size="lg" class="sm:text-xl">
            Employee Dashboard
        </flux:heading>

        <flux:text class=" text-zinc-500">
            Real-time overview of your workday, attendance, and leave balance.
        </flux:text>

        <div class="flex items-center justify-between sm:flex-col sm:items-end sm:justify-center">

            <div class="text-xs text-zinc-400 sm:text-sm">
                {{ now()->format('l') }}
            </div>

            <div class="text-sm font-semibold sm:text-lg">
                {{ now()->format('d M Y') }}
            </div>

        </div>

    </div>

    <!-- KPI CARDS -->
    <div class="grid gap-4 md:grid-cols-4">

        <flux:card class="p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-zinc-500">Status Today</div>
                    <div class="mt-1">
                        <flux:badge
                            color="{{ match ($todayAttendance['state']) {
                                'late' => 'yellow',
                                'early_checkout' => 'red',
                                'overtime' => 'blue',
                                default => 'green',
                            } }}">
                            {{ ucfirst($todayAttendance['state'] ?? 'Not checked') }}
                        </flux:badge>
                    </div>
                </div>

                <flux:icon name="bolt" class="w-6 h-6 text-zinc-400" />
            </div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-xs text-zinc-500">Check In</div>
            <div class="mt-1 text-lg font-semibold">
                {{ $todayAttendance['checkin'] ? \Carbon\Carbon::parse($todayAttendance['checkin'])->format('H:i') : '--:--' }}
            </div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-xs text-zinc-500">Check Out</div>
            <div class="mt-1 text-lg font-semibold">
                {{ $todayAttendance['checkout'] ? \Carbon\Carbon::parse($todayAttendance['checkout'])->format('H:i') : '--:--' }}
            </div>
        </flux:card>

        <flux:card class="p-4">
            <div class="text-xs text-zinc-500">Countdown</div>
            <div class="mt-1 text-lg font-semibold text-primary">
                {{ $countdown }}
            </div>
        </flux:card>

    </div>

    <!-- MAIN GRID -->
    <div class="grid gap-6 lg:grid-cols-3">

        <!-- SHIFT + PROGRESS -->
        <flux:card class="p-5 lg:col-span-2">

            <flux:heading size="sm">Today's Shift</flux:heading>

            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">

                <div class="p-3 rounded-xl bg-zinc-50">
                    <div class="text-xs text-zinc-500">Start Time</div>
                    <div class="text-base font-semibold">
                        {{ $shiftStart ?? '-' }}
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-zinc-50">
                    <div class="text-xs text-zinc-500">End Time</div>
                    <div class="text-base font-semibold">
                        {{ $shiftEnd ?? '-' }}
                    </div>
                </div>

            </div>

            <!-- Progress -->
            <div class="mt-6">

                @php
                    $progress = 0;

                    if ($todayAttendance['checkin'] && $shiftStart && $shiftEnd) {
                        $start = \Carbon\Carbon::parse($shiftStart);
                        $end = \Carbon\Carbon::parse($shiftEnd);

                        if ($end->lte($start)) {
                            $end->addDay();
                        }

                        $now = now();

                        $total = $start->diffInMinutes($end);
                        $done = min($start->diffInMinutes($now, false), $total);

                        $progress = $total > 0 ? max(0, min(100, ($done / $total) * 100)) : 0;
                    }
                @endphp

                <div class="text-xs text-zinc-500 mb-2">Shift Progress</div>

                <div class="w-full h-2 bg-zinc-200 rounded-full overflow-hidden">
                    <div class="h-full bg-primary" style="width: {{ $progress }}%"></div>
                </div>

            </div>

        </flux:card>

        <!-- LEAVE BALANCE -->
        <flux:card class="p-5">

            <flux:heading size="sm">Leave Balance</flux:heading>

            <div class="mt-4 space-y-4">

                @foreach ($this->leaveBalances as $balance)
                    @php
                        $percentage =
                            $balance->total_days > 0 ? ($balance->remaining_days / $balance->total_days) * 100 : 0;
                    @endphp

                    <div>

                        <div class="flex justify-between text-sm">
                            <span>{{ $balance->leaveType->name }}</span>
                            <span class="font-medium">
                                {{ $balance->remaining_days }} / {{ $balance->total_days }}
                            </span>
                        </div>

                        <div class="mt-1 h-2 bg-zinc-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500" style="width: {{ $percentage }}%"></div>
                        </div>

                    </div>
                @endforeach

            </div>

        </flux:card>

    </div>

    <!-- QUICK ACTION -->
    <flux:card class="p-5">

        <flux:heading size="sm">Quick Actions</flux:heading>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">

            <flux:button class="w-full" href="{{ route('employee.attendance-history') }}">
                View Attendance
            </flux:button>

            <flux:button class="w-full" variant="primary" href="{{ route('employee.attendances.index') }}">
                Check In / Out
            </flux:button>

            <flux:button class="w-full" variant="outline" href="{{ route('employee.leave') }}">
                Request Leave
            </flux:button>

        </div>

    </flux:card>

</div>
