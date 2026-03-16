<div class="space-y-6">

    <flux:card>

        <div class="flex items-center justify-between mb-4">

            <flux:button wire:click="previousMonth" icon="chevron-left" />

            <flux:heading size="lg">
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </flux:heading>

            <flux:button wire:click="nextMonth" icon="chevron-right" />

        </div>

        <div class="grid grid-cols-7 gap-2 text-sm">

            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="font-medium text-center">{{ $day }}</div>
            @endforeach

            @for ($day = 1; $day <= \Carbon\Carbon::create($year, $month)->daysInMonth; $day++)
                @php
                    $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                    $calendar = $this->calendar[$date] ?? null;
                @endphp

                <div class="p-2 border rounded">

                    <div class="text-xs">{{ $day }}</div>

                    @if ($calendar && $calendar->is_holiday)
                        <flux:badge color="red">
                            Holiday
                        </flux:badge>
                    @endif

                </div>
            @endfor

        </div>

    </flux:card>

</div>
