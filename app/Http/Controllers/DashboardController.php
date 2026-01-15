<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Dealer;
use App\Models\Activation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return $user->role === 'dealer'
            ? $this->dealerDashboard($user)
            : $this->adminDashboard($user->tenant_id);
    }

    /**
     * -----------------------------
     * ADMIN DASHBOARD
     * -----------------------------
     */
    private function adminDashboard(int $tenantId)
    {
        /* -----------------------------
         | DEVICE KPIs
         * ----------------------------- */
        $totalDevices = Device::where('tenant_id', $tenantId)->count();

        $inStockDevices = Device::where('tenant_id', $tenantId)
            ->where('status', 'stock')
            ->count();

        $allocatedDevices = Device::where('tenant_id', $tenantId)
            ->where('status', 'allocated')
            ->count();

        $activatedDevices = Device::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        /* -----------------------------
         | DEALER ALLOCATIONS
         * ----------------------------- */
        $dealerAllocations = Dealer::where('tenant_id', $tenantId)
            ->withCount([
                'allocations as allocated_count'
            ])
            ->get();

        /* -----------------------------------
        | PROVINCE-WISE ACTIVATIONS
        * ----------------------------------- */
        $provinceActivations = Activation::where('tenant_id', $tenantId)
            ->select(
                DB::raw('COALESCE(province, "Unknown") as province'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('province')
            ->orderBy('total', 'desc')
            ->get();
        /* -----------------------------
         | MODEL-WISE INVENTORY
         * ----------------------------- */
        $modelCounts = Device::where('tenant_id', $tenantId)
            ->select('model', DB::raw('COUNT(*) as total'))
            ->groupBy('model')
            ->get();

        /* -----------------------------
         | PROVINCE-WISE ACTIVATIONS
         | (FROM ACTIVATIONS TABLE)
         * ----------------------------- */
        $provinceActivations = Activation::where('tenant_id', $tenantId)
            ->select('province', DB::raw('COUNT(*) as total'))
            ->groupBy('province')
            ->get();

        /* -----------------------------
         | ACTIVATION TIMELINE (LAST 30 DAYS)
         * ----------------------------- */
        $activationTimeline = Activation::where('tenant_id', $tenantId)
            ->selectRaw('DATE(activation_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();

        return view('dashboard.admin', compact(
            'totalDevices',
            'inStockDevices',
            'allocatedDevices',
            'activatedDevices',
            'dealerAllocations',
            'modelCounts',
            'provinceActivations',
            'activationTimeline'
        ));
    }

    /**
     * -----------------------------
     * DEALER DASHBOARD
     * -----------------------------
     */
    private function dealerDashboard($user)
    {
        $dealerId = $user->dealer_id;

        $totalAllocated = Device::where('dealer_id', $dealerId)->count();

        $activated = Device::where('dealer_id', $dealerId)
            ->where('status', 'active')
            ->count();

        $pending = max($totalAllocated - $activated, 0);

        $cityWise = Activation::whereHas('device', function ($q) use ($dealerId) {
                $q->where('dealer_id', $dealerId);
            })
            ->select('city', DB::raw('COUNT(*) as total'))
            ->groupBy('city')
            ->get();

        return view('dashboard.dealer', compact(
            'totalAllocated',
            'activated',
            'pending',
            'cityWise'
        ));
    }
}
