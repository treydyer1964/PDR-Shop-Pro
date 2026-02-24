<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PDR Shop Pro' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        @click="sidebarOpen = false"
        style="display:none"
    ></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 transition-transform duration-200 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        {{-- Logo / Shop Name --}}
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-600">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()?->tenant?->name ?? config('app.name') }}</p>
                <p class="text-xs text-slate-400">PDR Shop Pro</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            <x-nav-item route="dashboard" icon="home">Dashboard</x-nav-item>
            <x-nav-item route="work-orders.index" icon="clipboard-document-list">Work Orders</x-nav-item>
            <x-nav-item route="customers.index" icon="users">Customers</x-nav-item>

            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Financial</p>
            </div>
            <x-nav-item route="commissions.index" icon="banknotes">Commissions</x-nav-item>
            <x-nav-item route="payroll.index" icon="document-check">Payroll</x-nav-item>

            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Operations</p>
            </div>
            <x-nav-item route="appointments.index" icon="calendar-days">Appointments</x-nav-item>
            <x-nav-item route="rentals.index" icon="key">Rentals</x-nav-item>
            <x-nav-item route="staff.index" icon="user-group">Staff</x-nav-item>

            @if(auth()->user()?->canManageStaff())
            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Admin</p>
            </div>
            <x-nav-item route="settings.index" icon="cog-6-tooth">Settings</x-nav-item>
            @endif
        </nav>

        {{-- User footer --}}
        <div class="shrink-0 border-t border-slate-800 p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ auth()->user()?->getRoleLabels() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content wrapper (offset by sidebar width on desktop) --}}
    <div class="flex min-h-full flex-col lg:pl-64">

        {{-- Top header bar --}}
        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-4 border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
            {{-- Mobile hamburger --}}
            <button
                @click="sidebarOpen = true"
                class="lg:hidden -ml-1 rounded-md p-1.5 text-slate-500 hover:bg-slate-100"
                aria-label="Open sidebar"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Page title --}}
            <div class="flex-1 min-w-0">
                @isset($header)
                    <h1 class="truncate text-lg font-semibold text-slate-900">{{ $header }}</h1>
                @endisset
            </div>

            {{-- Right-side header actions (buttons, search, etc.) --}}
            @isset($headerActions)
                <div class="flex shrink-0 items-center gap-2">{{ $headerActions }}</div>
            @endisset
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 sm:px-6">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
