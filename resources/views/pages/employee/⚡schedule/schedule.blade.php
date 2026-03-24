<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <flux:heading>My Schedule</flux:heading>
            <flux:text class="text-sm text-zinc-500">
                Your work schedule, shifts, and availability.
            </flux:text>
        </div>

        <div class="text-sm text-zinc-400">
            {{ now()->format('l, d M Y') }}
        </div>

    </div>

    {{-- TODAY (HIGHLIGHT CARD) --}}
    <flux:card class="p-4">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-xs text-zinc-500">
                    Today
                </div>

                @if (($today['type'] ?? null) === 'working')

                    <div class="mt-1 text-base font-semibold">
                        {{ $today['shift'] }}
                    </div>

                    <div class="text-sm text-zinc-500">
                        {{ $today['start'] }} - {{ $today['end'] }}
                        @if ($today['cross_midnight'])
                            <span class="text-blue-500">(Overnight)</span>
                        @endif
                    </div>
                @elseif (($today['type'] ?? null) === 'leave')
                    <div class="mt-1 text-base font-semibold text-yellow-600">
                        On Leave
                    </div>

                    <div class="text-sm text-zinc-500">
                        Approved leave
                    </div>
                @else
                    <div class="mt-1 text-base font-semibold text-zinc-500">
                        Off Day
                    </div>

                    <div class="text-sm text-zinc-400">
                        No schedule assigned
                    </div>

                @endif

            </div>

            <div>

                @if (($today['type'] ?? null) === 'working')
                    <flux:badge color="green">Working</flux:badge>
                @elseif (($today['type'] ?? null) === 'leave')
                    <flux:badge color="yellow">Leave</flux:badge>
                @else
                    <flux:badge color="zinc">Off</flux:badge>
                @endif

            </div>

        </div>

    </flux:card>

    {{-- WEEK VIEW (MOBILE FIRST LIST) --}}
    <flux:card class="p-4">

        <div class="flex items-center justify-between mb-3">
            <flux:heading size="sm">This Week</flux:heading>
            <span class="text-xs text-zinc-400">
                Weekly overview
            </span>
        </div>

        <div class="divide-y">

            @foreach ($week as $day)
                <div class="flex items-center justify-between py-3">

                    {{-- LEFT --}}
                    <div class="flex flex-col">

                        <div class="text-sm font-medium">
                            {{ $day['day'] }}
                        </div>

                        <div class="text-xs text-zinc-400">
                            {{ $day['date'] }}
                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="text-right">

                        @if ($day['type'] === 'working')
                            <div class="text-sm font-medium">
                                {{ $day['start'] }} - {{ $day['end'] }}
                            </div>

                            <div class="text-xs text-zinc-400">

                                {{ $day['shift'] }}

                                @if ($day['cross_midnight'])
                                    • Overnight
                                @endif

                            </div>
                        @elseif ($day['type'] === 'leave')
                            <flux:badge color="yellow">
                                Leave
                            </flux:badge>
                        @else
                            <flux:badge color="zinc">
                                Off
                            </flux:badge>
                        @endif

                    </div>

                </div>
            @endforeach

        </div>

    </flux:card>

</div>
