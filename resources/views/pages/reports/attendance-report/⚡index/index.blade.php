<div class="space-y-6">

    <!-- HEADER (mobile-first) -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <flux:heading size="lg">Attendance Report</flux:heading>
            <flux:text class=" text-zinc-500">
                Summary of employee attendance performance.
            </flux:text>
        </div>

        <div class="text-xs text-zinc-400 sm:text-sm">
            {{ \Carbon\Carbon::parse($start_date)->format('d M Y') }}
            →
            {{ \Carbon\Carbon::parse($end_date)->format('d M Y') }}
        </div>

    </div>

    <!-- FILTER -->
    <flux:card class="p-4">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <flux:input label="Start Date" type="date" wire:model.live="start_date" />

            <flux:input label="End Date" type="date" wire:model.live="end_date" />

            <flux:select label="Branch" wire:model.live="branch_id">
                <option value="">All Branches</option>
                @foreach ($this->branches as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>

            <flux:select label="Division" wire:model.live="division_id">
                <option value="">All Divisions</option>
                @foreach ($this->divisions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </flux:select>

        </div>

    </flux:card>

    <!-- SUMMARY (top stats) -->
    @php
        $totalUsers = count($this->report);
        $totalPresent = collect($this->report)->sum('present');
        $totalAbsent = collect($this->report)->sum('absent');
        $avgRate = $totalUsers > 0 ? round(collect($this->report)->avg('attendance_rate'), 2) : 0;
    @endphp

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Employees</div>
            <div class="text-lg font-semibold">{{ $totalUsers }}</div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Total Present</div>
            <div class="text-lg font-semibold text-green-500">
                {{ $totalPresent }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Total Absent</div>
            <div class="text-lg font-semibold text-red-500">
                {{ $totalAbsent }}
            </div>
        </flux:card>

        <flux:card class="p-3 text-center">
            <div class="text-xs text-zinc-500">Avg Attendance</div>
            <div class="text-lg font-semibold">
                {{ $avgRate }}%
            </div>
        </flux:card>

    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="space-y-3 lg:hidden">

        @forelse ($this->report as $row)
            <flux:card class="p-4">

                <div class="flex items-center justify-between">

                    <div>
                        <div class="font-medium">
                            {{ $row['user'] }}
                        </div>
                        <div class="text-xs text-zinc-500">
                            {{ $row['division'] }} • {{ $row['branch'] }}
                        </div>
                    </div>

                    <flux:badge color="{{ $row['attendance_rate'] >= 90 ? 'green' : 'red' }}">
                        {{ $row['attendance_rate'] }}%
                    </flux:badge>

                </div>

                <div class="grid grid-cols-4 gap-2 mt-4 text-xs text-center">

                    <div>
                        <div class="text-zinc-400">Present</div>
                        <div class="font-semibold text-green-500">
                            {{ $row['present'] }}
                        </div>
                    </div>

                    <div>
                        <div class="text-zinc-400">Late</div>
                        <div class="font-semibold text-yellow-500">
                            {{ $row['late'] }}
                        </div>
                    </div>

                    <div>
                        <div class="text-zinc-400">OT</div>
                        <div class="font-semibold text-blue-500">
                            {{ $row['overtime_days'] }}
                        </div>
                    </div>

                    <div>
                        <div class="text-zinc-400">Absent</div>
                        <div class="font-semibold text-red-500">
                            {{ $row['absent'] }}
                        </div>
                    </div>

                </div>

            </flux:card>

        @empty

            <flux:card class="p-6 text-center">
                <flux:text>No Attendance Data</flux:text>
            </flux:card>
        @endforelse

    </div>

    <!-- DESKTOP TABLE -->
    <flux:card class="hidden lg:block">

        <flux:table>

            <flux:table.columns>
                <flux:table.column>User</flux:table.column>
                <flux:table.column>Branch</flux:table.column>
                <flux:table.column>Division</flux:table.column>
                <flux:table.column>Working</flux:table.column>
                <flux:table.column>Present</flux:table.column>
                <flux:table.column>Late</flux:table.column>
                <flux:table.column>OT</flux:table.column>
                <flux:table.column>Absent</flux:table.column>
                <flux:table.column>Work</flux:table.column>
                <flux:table.column>OT</flux:table.column>
                <flux:table.column>Rate</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @foreach ($this->report as $row)
                    <flux:table.row>

                        <flux:table.cell>{{ $row['user'] }}</flux:table.cell>
                        <flux:table.cell>{{ $row['branch'] }}</flux:table.cell>
                        <flux:table.cell>{{ $row['division'] }}</flux:table.cell>
                        <flux:table.cell>{{ $row['working_days'] }}</flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="green">{{ $row['present'] }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="yellow">{{ $row['late'] }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="blue">{{ $row['overtime_days'] }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="red">{{ $row['absent'] }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>{{ $row['work_hours'] }}h</flux:table.cell>
                        <flux:table.cell>{{ $row['overtime_hours'] }}h</flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="{{ $row['attendance_rate'] >= 90 ? 'green' : 'red' }}">
                                {{ $row['attendance_rate'] }}%
                            </flux:badge>
                        </flux:table.cell>

                    </flux:table.row>
                @endforeach

            </flux:table.rows>

        </flux:table>

    </flux:card>

</div>
