<div class="space-y-4">

    {{-- HEADER --}}
    <flux:card class="p-4">
        <div class="flex items-center justify-between">
            <flux:button size="sm" wire:click="prev">←</flux:button>

            <div class="text-center">
                <div class="text-xs text-zinc-500">Work Calendar</div>
                <div class="text-base font-semibold">
                    {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
                </div>
            </div>

            <flux:button size="sm" wire:click="next">→</flux:button>
        </div>
    </flux:card>

    {{-- LEGEND --}}
    <flux:card class="p-3">
        <div class="flex flex-wrap gap-2 text-xs">
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span>Working</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                <span>Leave</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                <span>Holiday</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-zinc-400"></span>
                <span>Off</span>
            </div>
        </div>
    </flux:card>

    {{-- CALENDAR --}}
    <flux:card class="p-3">

        <div class="grid grid-cols-7 mb-2 text-[10px] text-center text-zinc-400">
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
            <div>Sun</div>
        </div>

        <div class="grid grid-cols-7 gap-1">

            @foreach ($calendar as $day)
                @if ($day === null)
                    <div></div>
                @else
                    @php
                        $isToday = $day['date'] === now()->toDateString();

                        $color = match ($day['type']) {
                            'working' => 'bg-green-50 text-green-700',
                            'leave' => 'bg-yellow-50 text-yellow-700',
                            'holiday' => 'bg-red-50 text-red-700',
                            default => 'bg-zinc-50 text-zinc-500',
                        };
                    @endphp

                    <button wire:click="showDetailModal({{ json_encode($day) }})"
                        class="rounded-md p-1 min-h-[60px] flex flex-col justify-between text-[10px] border {{ $isToday ? 'border-blue-500' : 'border-transparent' }} {{ $color }}">

                        <div class="flex justify-between">
                            <span class="font-semibold">{{ $day['day'] }}</span>
                            @if ($isToday)
                                <span class="text-[8px] text-blue-500">●</span>
                            @endif
                        </div>

                        <div class="text-[9px]">

                            @if ($day['type'] === 'working')
                                <div>{{ $day['start'] }}</div>
                                <div>{{ $day['end'] }}</div>
                            @elseif ($day['type'] === 'leave')
                                <div>Leave</div>
                            @elseif ($day['type'] === 'holiday')
                                <div>Holiday</div>
                            @else
                                <div>Off</div>
                            @endif

                        </div>

                    </button>
                @endif
            @endforeach

        </div>

    </flux:card>

    {{-- MODAL (FLUX CORRECT PATTERN) --}}
    <flux:modal name="calendar-detail" class="md:w-96">

        <div class="space-y-4">

            <flux:heading size="lg">
                {{ \Carbon\Carbon::parse($selectedDay['date'] ?? now())->format('l, d M Y') }}
            </flux:heading>

            @if (($selectedDay['type'] ?? null) === 'working')

                <div class="space-y-2 text-sm">

                    <div class="flex justify-between">
                        <span>Status</span>
                        <flux:badge color="green">Working</flux:badge>
                    </div>

                    <div class="flex justify-between">
                        <span>Shift</span>
                        <span>{{ $selectedDay['shift'] }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Start</span>
                        <span>{{ $selectedDay['start'] }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>End</span>
                        <span>{{ $selectedDay['end'] }}</span>
                    </div>

                    @if ($selectedDay['cross_midnight'] ?? false)
                        <div class="text-xs text-blue-600">
                            This shift continues to the next day
                        </div>
                    @endif

                </div>
            @elseif (($selectedDay['type'] ?? null) === 'leave')
                <div class="space-y-2 text-sm">

                    <div class="flex justify-between">
                        <span>Status</span>
                        <flux:badge color="yellow">On Leave</flux:badge>
                    </div>

                    <div class="text-xs text-zinc-500">
                        Approved leave
                    </div>

                </div>
            @elseif (($selectedDay['type'] ?? null) === 'holiday')
                <div class="space-y-2 text-sm">

                    <div class="flex justify-between">
                        <span>Status</span>
                        <flux:badge color="red">Holiday</flux:badge>
                    </div>

                </div>
            @else
                <div class="space-y-2 text-sm">

                    <div class="flex justify-between">
                        <span>Status</span>
                        <flux:badge color="zinc">Off Day</flux:badge>
                    </div>

                </div>

            @endif

            <div class="pt-4 text-right">
                <flux:button variant="ghost" x-on:click="$flux.modal('calendar-detail').close()">
                    Close
                </flux:button>
            </div>

        </div>

    </flux:modal>

</div>
