<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Placeholder KPI cards — will be replaced with real data in Phase 16 --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Open Jobs</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">—</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Jobs This Month</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">—</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Revenue MTD</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">—</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Unpaid Commissions</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">—</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-sm font-semibold text-slate-900">Welcome back, {{ auth()->user()->name }}!</h2>
        <p class="mt-1 text-sm text-slate-500">
            PDR Shop Pro is being built. Work Orders, Commissions, and more are coming soon.
        </p>
    </div>
</x-app-layout>
