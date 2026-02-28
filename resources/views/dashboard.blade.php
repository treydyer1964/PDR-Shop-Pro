<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('analytics.index') }}" wire:navigate
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Full Analytics
        </a>
    </x-slot>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Open Jobs</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $openJobs }}</p>
            <p class="mt-1 text-xs text-slate-400">Active, not yet delivered</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Jobs This Month</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $jobsThisMonth }}</p>
            <p class="mt-1 text-xs text-slate-400">Created {{ now()->format('M Y') }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Revenue MTD</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">
                @if($revenueMtd > 0)
                    ${{ number_format($revenueMtd, 0) }}
                @else
                    $0
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-400">Delivered {{ now()->format('M Y') }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Unpaid Commissions</p>
            <p class="mt-2 text-3xl font-bold {{ $unpaidCommissions > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                ${{ number_format($unpaidCommissions, 0) }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Pending pay run</p>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('work-orders.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 hover:ring-blue-300 transition-all text-center">
            <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            <span class="text-xs font-medium text-slate-600">Work Orders</span>
        </a>
        <a href="{{ route('commissions.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 hover:ring-blue-300 transition-all text-center">
            <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            <span class="text-xs font-medium text-slate-600">Commissions</span>
        </a>
        <a href="{{ route('payroll.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 hover:ring-blue-300 transition-all text-center">
            <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
            </svg>
            <span class="text-xs font-medium text-slate-600">Payroll</span>
        </a>
        <a href="{{ route('rentals.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 hover:ring-blue-300 transition-all text-center">
            <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            <span class="text-xs font-medium text-slate-600">Rentals</span>
        </a>
        <a href="{{ route('appointments.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 hover:ring-blue-300 transition-all text-center">
            <svg class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
            </svg>
            <span class="text-xs font-medium text-slate-600">Appointments</span>
        </a>
        <a href="{{ route('analytics.index') }}" wire:navigate
           class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 shadow-sm ring-1 ring-blue-200 bg-blue-50 hover:ring-blue-400 transition-all text-center">
            <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <span class="text-xs font-medium text-blue-600">Analytics</span>
        </a>
    </div>

    {{-- Welcome bar --}}
    <div class="mt-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <p class="text-sm font-semibold text-slate-900">Welcome back, {{ auth()->user()->name }}!</p>
        <p class="mt-1 text-sm text-slate-500">
            {{ now()->format('l, F j, Y') }}
        </p>
    </div>
</x-app-layout>
