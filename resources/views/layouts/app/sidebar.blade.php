<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 h-screen flex flex-col">

        {{-- HEADER (fixed height) --}}
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- MAIN NAV (FULL HEIGHT + SCROLLABLE) --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- MENU LIST --}}
            <flux:sidebar.nav class="flex-1 overflow-y-auto">
                @foreach (config('sidebar') as $group)
                    <x-sidebar-item :label="$group['label']" :icon="$group['icon'] ?? 'circle'" :route="$group['route'] ?? null" :permission="$group['permission'] ?? null"
                        :role="$group['role'] ?? null" :children="$group['children'] ?? []" />
                @endforeach
            </flux:sidebar.nav>

            {{-- STATIC BOTTOM MENU --}}
            {{-- <flux:sidebar.nav class="border-t border-zinc-200 dark:border-zinc-700">
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                    target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                    target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav> --}}

        </div>

        {{-- USER MENU (FIXED BOTTOM, NOT SCROLLING) --}}
        <div class="sticky bottom-0 bg-zinc-50 dark:bg-zinc-900 z-10">
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </div>

    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    <livewire:alert-modal />

    @fluxScripts
    @stack('scripts')
</body>

</html>
