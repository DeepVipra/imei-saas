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
         | FILTER INPUTS
         * ----------------------------- */
        $from      = request('from');
        $to        = request('to');
        $dealerId  = request('dealer_id');
        $model     = request('model');
        $province  = request('province');

        /* -----------------------------
         | DEVICE KPIs (NOT FILTERED)
         * ----------------------------- */
        $totalDevices = Device::where('tenant_id', $tenantId)->count();

        $inStockDevices = Device::where('tenant_id', $tenantId)
            ->where('status', 'stock')
            ->count();

        $allocatedDevices = Device::where('tenant_id', $tenantId)
            ->where('status', 'allocated')
            ->count();

        /* -----------------------------
         | BASE ACTIVATION QUERY (FILTERED)
         * ----------------------------- */
        $activationQuery = Activation::where('activations.tenant_id', $tenantId);

        if ($from) {
            $activationQuery->whereDate('activations.activation_date', '>=', $from);
        }

        if ($to) {
            $activationQuery->whereDate('activations.activation_date', '<=', $to);
        }

        if ($dealerId) {
            $activationQuery->whereHas('deviceAllocation', function ($q) use ($dealerId) {
                $q->where('device_allocations.dealer_id', $dealerId);
            });
        }

        if ($model) {
            $activationQuery->whereHas('device', function ($q) use ($model) {
                $q->where('devices.model', $model);
            });
        }

        if ($province) {
            $activationQuery->where('activations.province', $province);
        }

        /* -----------------------------
         | ACTIVATED DEVICES (FILTERED)
         * ----------------------------- */
        $activatedDevices = (clone $activationQuery)->count();

        /* -----------------------------
         | DEALER-WISE ALLOCATION & ACTIVATION
         | Allocation = lifetime
         | Activation = filtered (billable)
         * ----------------------------- */
        $dealerStats = Dealer::where('dealers.tenant_id', $tenantId)
            ->withCount([
                'allocations as allocated_count',
                'activations as activated_count' => function ($q) use (
                    $tenantId, $from, $to, $model, $province
                ) {
                    $q->where('activations.tenant_id', $tenantId);

                    if ($from) {
                        $q->whereDate('activations.activation_date', '>=', $from);
                    }

                    if ($to) {
                        $q->whereDate('activations.activation_date', '<=', $to);
                    }

                    if ($model) {
                        $q->whereHas('device', function ($dq) use ($model) {
                            $dq->where('devices.model', $model);
                        });
                    }

                    if ($province) {
                        $q->where('activations.province', $province);
                    }
                }
            ])
            ->get();

        /* -----------------------------
         | MODEL-WISE INVENTORY (NOT FILTERED)
         * ----------------------------- */
        $modelCounts = Device::where('tenant_id', $tenantId)
            ->select('model', DB::raw('COUNT(*) as total'))
            ->groupBy('model')
            ->get();

        /* -----------------------------
         | PROVINCE-WISE ACTIVATIONS (FILTERED)
         * ----------------------------- */
        $provinceActivations = (clone $activationQuery)
            ->select(
                DB::raw('COALESCE(activations.province, "Unknown") as province'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('activations.province')
            ->orderBy('total', 'desc')
            ->get();

        /* -----------------------------
         | ACTIVATION TIMELINE (FILTERED)
         * ----------------------------- */
        $activationTimeline = (clone $activationQuery)
            ->selectRaw('DATE(activations.activation_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();

        return view('dashboard.admin', compact(
            'totalDevices',
            'inStockDevices',
            'allocatedDevices',
            'activatedDevices',
            'dealerStats',
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
