@php
    if (!$authorized()) {
        return;
    }
@endphp

@if (empty($children))
    <flux:sidebar.item icon="{{ $icon }}" :href="$route ? route($route) : '#'"
        :current="$route ? request()->routeIs($route) : false" wire:navigate>
        {{ __($label) }}
    </flux:sidebar.item>
@else
    <flux:sidebar.group :heading="__($label)">
        @foreach ($children as $child)
            <x-sidebar-item :label="$child['label']" :icon="$child['icon'] ?? 'circle'" :route="$child['route'] ?? null" :permission="$child['permission'] ?? null" :role="$child['role'] ?? null"
                :children="$child['children'] ?? []" />
        @endforeach
    </flux:sidebar.group>
@endif
