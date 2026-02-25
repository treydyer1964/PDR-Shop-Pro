<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;

class PhotoShareController extends Controller
{
    public function show(Request $request, WorkOrder $workOrder)
    {
        abort_unless($request->hasValidSignature(), 403);

        $photos = $workOrder->photos()->get()->groupBy(fn($p) => $p->category->value);

        return view('photos.share', compact('workOrder', 'photos'));
    }
}
