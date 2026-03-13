<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PDR Shop Pro' }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PDR Shop">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @isset($headScripts){{ $headScripts }}@endisset
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
            @if(auth()->user()?->canAccessPayroll())
            <x-nav-item route="payroll.index" icon="document-check">Payroll</x-nav-item>
            @endif

            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Operations</p>
            </div>
            <x-nav-item route="appointments.index" icon="calendar-days">Appointments</x-nav-item>
            <a href="{{ route('calendar') }}"
               @class([
                   'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ml-5',
                   'bg-slate-800 text-white' => request()->routeIs('calendar'),
                   'text-slate-400 hover:bg-slate-800 hover:text-white' => !request()->routeIs('calendar'),
               ])>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                Calendar
            </a>
            @if(!auth()->user()?->isFieldStaff())
            <x-nav-item route="rentals.index" icon="key">Rentals</x-nav-item>
            <x-nav-item route="staff.index" icon="user-group">Staff</x-nav-item>
            @if(auth()->user()?->canAccessAnalytics())
            <x-nav-item route="storm-events.index" icon="cloud">Storms</x-nav-item>
            <x-nav-item route="hail-tracker.index" icon="cloud-arrow-down">Hail Tracker</x-nav-item>
            @endif
            @endif
            @if(auth()->user()?->canCreateWorkOrders())
            <x-nav-item route="leads.index" icon="map-pin">Pins</x-nav-item>
            <a href="{{ route('leads.map') }}"
               @class([
                   'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ml-5',
                   'bg-slate-800 text-white' => request()->routeIs('leads.map'),
                   'text-slate-400 hover:bg-slate-800 hover:text-white' => !request()->routeIs('leads.map'),
               ])>
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.159.69.159 1.006 0z" />
                </svg>
                Map
            </a>
            @endif

            @if(auth()->user()?->canAccessAnalytics())
            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Reporting</p>
            </div>
            <x-nav-item route="analytics.index" icon="chart-bar">Analytics</x-nav-item>
            @endif

            @if(auth()->user()?->canManageStaff())
            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Admin</p>
            </div>
            <x-nav-item route="settings.index" icon="cog-6-tooth">Settings</x-nav-item>
            @elseif(auth()->user()?->canManageTerritories())
            <div class="pt-4 pb-1 px-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Admin</p>
            </div>
            <x-nav-item route="settings.territories" icon="cog-6-tooth">Settings</x-nav-item>
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

    @isset($footerScripts){{ $footerScripts }}@endisset
    @livewireScripts

    {{-- Mobile-safe PDF opener: uses Web Share API on iOS PWA, falls back to window.open() --}}
    <script>
    function openPdf(url, filename, btn) {
        var origHtml = btn ? btn.innerHTML : null;
        function restore() { if (btn) { btn.disabled = false; btn.innerHTML = origHtml; } }
        function fallback() { restore(); window.open(url, '_blank'); }

        if (btn) { btn.disabled = true; btn.innerHTML = 'Loading&hellip;'; }

        if (navigator.canShare) {
            fetch(url)
                .then(function (r) { return r.blob(); })
                .then(function (blob) {
                    restore();
                    var file = new File([blob], filename, { type: 'application/pdf' });
                    if (navigator.canShare({ files: [file] })) {
                        navigator.share({ files: [file], title: filename })
                            .catch(function (err) { if (err.name !== 'AbortError') fallback(); });
                    } else {
                        fallback();
                    }
                })
                .catch(fallback);
        } else {
            restore();
            fallback();
        }
    }

    // Rental invoice opener — opens PDF + pre-filled mailto with claim # subject
    function openRentalInvoice(url, filename, claimNumber, adjusterEmail, btn) {
        var subject = claimNumber ? 'Claim: ' + claimNumber : 'Rental Reimbursement';
        var mailto  = 'mailto:' + (adjusterEmail || '')
                    + '?subject=' + encodeURIComponent(subject)
                    + '&body=' + encodeURIComponent('Please see the attached rental invoice.');

        var origHtml = btn ? btn.innerHTML : null;
        function restore() { if (btn) { btn.disabled = false; btn.innerHTML = origHtml; } }

        if (btn) { btn.disabled = true; btn.innerHTML = 'Loading&hellip;'; }

        if (navigator.canShare) {
            // iOS PWA: share sheet lets user pick Mail and the PDF comes along
            fetch(url)
                .then(function (r) { return r.blob(); })
                .then(function (blob) {
                    restore();
                    var file = new File([blob], filename, { type: 'application/pdf' });
                    if (navigator.canShare({ files: [file] })) {
                        navigator.share({ files: [file], title: subject })
                            .catch(function (err) { if (err.name !== 'AbortError') window.open(url, '_blank'); });
                    } else {
                        window.open(url, '_blank');
                    }
                })
                .catch(function () { restore(); window.open(url, '_blank'); });
        } else {
            // Desktop: open PDF in new tab, then open pre-filled mailto
            restore();
            window.open(url, '_blank');
            setTimeout(function () { window.location.href = mailto; }, 500);
        }
    }
    </script>

    {{-- PWA Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
