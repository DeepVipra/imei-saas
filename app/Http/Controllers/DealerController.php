<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    public function index()
    {
        $dealers = Dealer::where('tenant_id', auth()->user()->tenant_id)->get();
        return view('dealers.index', compact('dealers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        Dealer::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name
        ]);

        return back()->with('success', 'Dealer created');
    }
}
