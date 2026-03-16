<div class="space-y-6">

    <div>
        <flux:heading>Enterprise Leave Calendar</flux:heading>
        <flux:text class="text-sm text-zinc-500">
            Monitor employee leave schedules, detect conflicts, and review leave coverage.
        </flux:text>
    </div>


    {{-- Legend --}}

    <flux:card>

        <div class="flex flex-wrap gap-6 text-xs">

            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-300 rounded"></div>
                <span>Approved Leave</span>
            </div>

            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-yellow-300 rounded"></div>
                <span>Pending Approval</span>
            </div>

            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-300 rounded"></div>
                <span>Rejected Leave</span>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-zinc-400 text-xs">W</span>
                <span>Weekend</span>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-red-500">⚠</span>
                <span>Leave Conflict</span>
            </div>

        </div>

    </flux:card>


    {{-- Filters --}}

    <flux:card>

        <div class="grid md:grid-cols-4 gap-4">

            <flux:select label="Branch" wire:model.live="branchId">
                <option value="">All Branches</option>
                @foreach ($this->branches() as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach
            </flux:select>

            <flux:select label="Division" wire:model.live="divisionId">
                <option value="">All Divisions</option>
                @foreach ($this->divisions() as $id => $name)
                    <option value="{{ $id }}">
                        {{ $name }}
                    </option>
                @endforeach
            </flux:select>

        </div>

    </flux:card>


    {{-- Month Navigation --}}

    <flux:card>

        <div class="flex items-center justify-between">

            <flux:button size="sm" wire:click="prevMonth">
                Previous
            </flux:button>

            <flux:heading size="sm">
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </flux:heading>

            <flux:button size="sm" wire:click="nextMonth">
                Next
            </flux:button>

        </div>

    </flux:card>


    {{-- Calendar --}}

    <flux:card class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead>

                <tr class="border-b bg-zinc-50">

                    <th class="text-left py-3 w-52">
                        Employee
                    </th>

                    @foreach ($calendar as $day)
                        <th class="text-center py-2 min-w-[55px]">

                            <div class="flex flex-col items-center">

                                <span class="text-xs font-medium text-zinc-600">
                                    {{ $day['day'] }}
                                </span>

                                {{-- leave counter --}}

                                @if (($conflicts[$day['date']] ?? 0) > 0)
                                    <span class="text-[10px] text-zinc-400">
                                        {{ $conflicts[$day['date']] }} leave
                                    </span>
                                @endif

                                {{-- conflict indicator --}}

                                @if (($conflicts[$day['date']] ?? 0) > 3)
                                    <span class="text-red-500 text-xs">
                                        ⚠
                                    </span>
                                @endif

                            </div>

                        </th>
                    @endforeach

                </tr>

            </thead>


            <tbody>

                @foreach ($users as $user)
                    <tr class="border-b hover:bg-zinc-50">

                        {{-- employee column --}}

                        <td class="py-3 pr-4">

                            <div class="flex flex-col">

                                <span class="font-medium">
                                    {{ $user['name'] }}
                                </span>

                                <span class="text-xs text-zinc-400">
                                    {{ $user['position']['title'] ?? '' }}
                                </span>

                                <span class="text-xs text-zinc-400">
                                    {{ $user['position']['division']['name'] ?? '' }}
                                </span>

                            </div>

                        </td>


                        {{-- calendar cells --}}

                        @foreach ($calendar as $day)
                            @php
                                $leaves = $leaveMap[$user['id']][$day['date']] ?? [];
                            @endphp

                            <td class="py-2 text-center align-top">

                                <div class="flex flex-col items-center gap-1">

                                    @forelse ($leaves as $leave)
                                        @php
                                            $color = match ($leave->status) {
                                                'approved' => 'bg-green-200 text-green-800',
                                                'pending' => 'bg-yellow-200 text-yellow-800',
                                                'rejected' => 'bg-red-200 text-red-800',
                                                default => 'bg-zinc-200 text-zinc-700',
                                            };

                                            $code = strtoupper(substr($leave->leaveType->name, 0, 2));
                                        @endphp

                                        <div class="px-2 py-[2px] rounded text-[10px] font-medium {{ $color }}"
                                            title="
                                                Employee : {{ $leave->user->name }}
                                                Leave : {{ $leave->leaveType->name }}
                                                Status : {{ $leave->status }}
                                                Period : {{ $leave->start_date }} - {{ $leave->end_date }}
                                            ">

                                            {{ $code }}

                                        </div>

                                    @empty

                                        @if ($day['weekend'])
                                            <span class="text-zinc-300 text-xs">
                                                W
                                            </span>
                                        @else
                                            <span class="text-zinc-200 text-xs">
                                                •
                                            </span>
                                        @endif
                                    @endforelse

                                </div>

                            </td>
                        @endforeach

                    </tr>
                @endforeach

            </tbody>

        </table>

    </flux:card>

</div>
