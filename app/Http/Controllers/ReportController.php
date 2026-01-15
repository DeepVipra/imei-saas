<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Device,
    Dealer,
    Activation
};

class ReportController extends Controller
{
    /**
     * DEVICE / INVENTORY REPORT
     */
    public function devices(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Device::where('tenant_id', $tenantId);

        // Dealer scope (for dealer login)
        if ($user->role === 'dealer') {
            $query->whereHas('allocation', function ($q) use ($user) {
                $q->where('dealer_id', $user->dealer_id);
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('dealer_id') && $user->role === 'admin') {
            $query->whereHas('allocation', function ($q) use ($request) {
                $q->where('dealer_id', $request->dealer_id);
            });
        }

        $devices = $query->latest()->paginate(25)->withQueryString();

        $dealers = Dealer::where('tenant_id', $tenantId)->get();

        return view('reports.devices', compact('devices', 'dealers'));
    }

    /**
     * ACTIVATION REPORT
     */
    public function activations(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Activation::where('tenant_id', $tenantId);

        // Dealer scope
        if ($user->role === 'dealer') {
            $query->whereHas('device.allocation', function ($q) use ($user) {
                $q->where('dealer_id', $user->dealer_id);
            });
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('activation_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('activation_date', '<=', $request->to_date);
        }

        // Geography
        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $activations = $query->latest()->paginate(25)->withQueryString();

        return view('reports.activations', compact('activations'));
    }
}
