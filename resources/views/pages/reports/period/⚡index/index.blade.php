<div class="space-y-6">

    <div>
        <flux:heading>Attendance Report</flux:heading>

        <flux:text class="mt-1 text-sm text-zinc-500">
            Attendance summary for the selected period.
        </flux:text>
    </div>

    <flux:card>

        <div class="grid gap-3 sm:grid-cols-4">

            <flux:input label="Start Date" type="date" wire:model.live="start_date" />

            <flux:input label="End Date" type="date" wire:model.live="end_date" />

            <flux:select label="Branch" wire:model.live="branch_id">

                <option value="">
                    All Branches
                </option>

                @foreach ($this->branches as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach

            </flux:select>

            <flux:select label="Division" wire:model.live="division_id">

                <option value="">
                    All Divisions
                </option>

                @foreach ($this->divisions as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach

            </flux:select>

        </div>

    </flux:card>

    <flux:card>

        <flux:table>

            <flux:table.columns>

                <flux:table.column>User</flux:table.column>
                <flux:table.column>Branch</flux:table.column>
                <flux:table.column>Division</flux:table.column>
                <flux:table.column>Working Days</flux:table.column>
                <flux:table.column>Present</flux:table.column>
                <flux:table.column>Late</flux:table.column>
                <flux:table.column>Overtime Days</flux:table.column>
                <flux:table.column>Absent</flux:table.column>
                <flux:table.column>Work Hours</flux:table.column>
                <flux:table.column>Overtime Hours</flux:table.column>
                <flux:table.column>Attendance Rate</flux:table.column>

            </flux:table.columns>

            <flux:table.rows>

                @forelse ($this->report as $row)
                    <flux:table.row>

                        <flux:table.cell>
                            {{ $row['user'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $row['branch'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $row['division'] }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $row['working_days'] }}
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="green">
                                {{ $row['present'] }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="yellow">
                                {{ $row['late'] }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="purple">
                                {{ $row['overtime_days'] }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="red">
                                {{ $row['absent'] }}
                            </flux:badge>

                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $row['work_hours'] }} h
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $row['overtime_hours'] }} h
                        </flux:table.cell>

                        <flux:table.cell>

                            <flux:badge color="{{ $row['attendance_rate'] >= 90 ? 'green' : 'red' }}">

                                {{ $row['attendance_rate'] }} %

                            </flux:badge>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="11">

                            <div class="flex flex-col items-center justify-center py-10">

                                <flux:heading size="sm">
                                    No Attendance Data
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    No attendance records were found for the selected period.
                                </flux:text>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>
                @endforelse

            </flux:table.rows>

        </flux:table>

    </flux:card>

</div>
