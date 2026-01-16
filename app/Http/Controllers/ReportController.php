<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Dealer;
use App\Models\Activation;
use App\Exports\DevicesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * -------------------------------------------------
     * DEVICE / INVENTORY REPORT (TABLE VIEW)
     * -------------------------------------------------
     */
    public function devices(Request $request)
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Device::query()
            ->leftJoin('device_allocations', 'devices.id', '=', 'device_allocations.device_id')
            ->where('devices.tenant_id', $tenantId)
            ->select('devices.*')
            ->distinct();

        /* -----------------------------
         | STATUS FILTER (UI → DB)
         * ----------------------------- */
        if ($request->filled('status')) {
            $statusMap = [
                'in_stock'  => 'stock',
                'stock'     => 'stock',
                'allocated' => 'allocated',
                'activated' => 'active',
                'active'    => 'active',
            ];

            $uiStatus = strtolower($request->status);

            if (isset($statusMap[$uiStatus])) {
                $query->where('devices.status', $statusMap[$uiStatus]);
            }
        }

        /* -----------------------------
         | DEALER FILTER (ADMIN)
         * ----------------------------- */
        if ($request->filled('dealer_id') && $user->role === 'admin') {
            $query->whereIn('devices.status', ['allocated', 'active'])
                  ->where('device_allocations.dealer_id', $request->dealer_id);
        }

        /* -----------------------------
         | DEALER SCOPE (DEALER LOGIN)
         * ----------------------------- */
        if ($user->role === 'dealer') {
            $query->whereIn('devices.status', ['allocated', 'active'])
                  ->where('device_allocations.dealer_id', $user->dealer_id);
        }

        /* -----------------------------
         | MODEL FILTER
         * ----------------------------- */
        if ($request->filled('model')) {
            $query->where('devices.model', $request->model);
        }

        $devices = $query
            ->with(['allocation.dealer', 'activation'])
            ->orderByDesc('devices.id')
            ->paginate(25)
            ->withQueryString();

        $dealers = Dealer::where('tenant_id', $tenantId)->get();

        return view('reports.devices', compact('devices', 'dealers'));
    }

    /**
     * -------------------------------------------------
     * DEVICE / INVENTORY REPORT (EXCEL EXPORT)
     * -------------------------------------------------
     */
    public function exportDevices(Request $request)
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Device::query()
            ->leftJoin('device_allocations', 'devices.id', '=', 'device_allocations.device_id')
            ->where('devices.tenant_id', $tenantId)
            ->select('devices.*')
            ->distinct();

        /* -----------------------------
         | STATUS FILTER (UI → DB)
         * ----------------------------- */
        if ($request->filled('status')) {
            $statusMap = [
                'in_stock'  => 'stock',
                'stock'     => 'stock',
                'allocated' => 'allocated',
                'activated' => 'active',
                'active'    => 'active',
            ];

            $uiStatus = strtolower($request->status);

            if (isset($statusMap[$uiStatus])) {
                $query->where('devices.status', $statusMap[$uiStatus]);
            }
        }

        /* -----------------------------
         | DEALER FILTER (ADMIN)
         * ----------------------------- */
        if ($request->filled('dealer_id') && $user->role === 'admin') {
            $query->whereIn('devices.status', ['allocated', 'active'])
                  ->where('device_allocations.dealer_id', $request->dealer_id);
        }

        /* -----------------------------
         | DEALER SCOPE (DEALER LOGIN)
         * ----------------------------- */
        if ($user->role === 'dealer') {
            $query->whereIn('devices.status', ['allocated', 'active'])
                  ->where('device_allocations.dealer_id', $user->dealer_id);
        }

        /* -----------------------------
         | MODEL FILTER
         * ----------------------------- */
        if ($request->filled('model')) {
            $query->where('devices.model', $request->model);
        }

        $devices = $query
            ->with(['allocation.dealer'])
            ->orderByDesc('devices.id')
            ->get();

        return Excel::download(
            new DevicesExport($devices),
            'devices-report.xlsx'
        );
    }

    /**
     * -------------------------------------------------
     * ACTIVATION REPORT
     * -------------------------------------------------
     */
    public function activations(Request $request)
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Activation::where('activations.tenant_id', $tenantId);

        if ($user->role === 'dealer') {
            $query->whereHas('device.allocation', function ($q) use ($user) {
                $q->where('dealer_id', $user->dealer_id);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('activations.activation_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('activations.activation_date', '<=', $request->to_date);
        }

        if ($request->filled('province')) {
            $query->where('activations.province', $request->province);
        }

        if ($request->filled('city')) {
            $query->where('activations.city', $request->city);
        }

        $activations = $query
            ->with(['device', 'device.allocation.dealer'])
            ->orderByDesc('activations.activation_date')
            ->paginate(25)
            ->withQueryString();

        return view('reports.activations', compact('activations'));
    }
}
