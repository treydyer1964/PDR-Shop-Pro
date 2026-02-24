<?php

namespace App\Http\Controllers;

use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        return view('staff.index');
    }

    public function create()
    {
        return view('staff.create');
    }

    public function show(User $staff)
    {
        abort_unless($staff->tenant_id === auth()->user()->tenant_id, 403);
        return view('staff.show', compact('staff'));
    }

    public function edit(User $staff)
    {
        abort_unless($staff->tenant_id === auth()->user()->tenant_id, 403);
        return view('staff.edit', compact('staff'));
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->tenant_id === auth()->user()->tenant_id, 403);
        abort_if($staff->id === auth()->id(), 403, 'You cannot delete your own account.');

        $staff->delete();

        session()->flash('success', 'Staff member deleted.');
        return redirect()->route('staff.index');
    }
}
