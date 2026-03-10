<?php

namespace App\Http\Controllers;

use App\Models\Lead;

class LeadController extends Controller
{
    public function index()
    {
        return view('leads.index');
    }

    public function map()
    {
        return view('leads.index', ['forceMapView' => true]);
    }

    public function create()
    {
        abort_unless(auth()->user()->canCreateWorkOrders(), 403);
        return view('leads.create');
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();
        abort_unless($lead->tenant_id === $user->tenant_id, 403);

        if ($user->isFieldStaff()) {
            abort_unless($lead->assigned_to === $user->id, 403);
        }

        $lead->load(['assignedUser', 'territory', 'stormEvent', 'convertedWorkOrder.customer', 'creator']);
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();
        abort_unless($lead->tenant_id === $user->tenant_id, 403);

        if ($user->isFieldStaff()) {
            abort_unless($lead->assigned_to === $user->id, 403);
        }

        return view('leads.edit', compact('lead'));
    }
}
