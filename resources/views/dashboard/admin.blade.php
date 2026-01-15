@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Admin Dashboard</h2>

    {{-- KPI CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Total Devices</h6>
                <strong>{{ $totalDevices }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Allocated</h6>
                <strong>{{ $allocatedDevices }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>Activated</h6>
                <strong>{{ $activatedDevices }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h6>In Stock</h6>
                <strong>{{ $inStockDevices }}</strong>
            </div>
        </div>
    </div>

    {{-- DEALER ALLOCATION --}}
    <h4 class="mt-4">Allocation per Dealer</h4>
    <canvas id="dealerChart" height="120"></canvas>

    {{-- PROVINCE-WISE ACTIVATION --}}
    <h4 class="mt-5">Activated Devices by Province</h4>
    <canvas id="provinceChart" height="120"></canvas>

    {{-- MODEL DISTRIBUTION --}}
    <h4 class="mt-5">Model Distribution</h4>
    <canvas id="modelChart" height="120"></canvas>

    {{-- ACTIVATION TIMELINE --}}
    <h4 class="mt-5">Activation Timeline (Last 30 Days)</h4>
    <canvas id="timelineChart" height="120"></canvas>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------
     | DEALER ALLOCATION CHART
     * ----------------------------- */
    new Chart(document.getElementById('dealerChart'), {
        type: 'bar',
        data: {
            labels: @json($dealerAllocations->pluck('name')),
            datasets: [{
                label: 'Allocated Devices',
                data: @json($dealerAllocations->pluck('allocated_count')),
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    /* -----------------------------
     | PROVINCE-WISE ACTIVATION CHART ✅
     * ----------------------------- */
    new Chart(document.getElementById('provinceChart'), {
        type: 'bar',
        data: {
            labels: @json($provinceActivations->pluck('province')),
            datasets: [{
                label: 'Activated Devices',
                data: @json($provinceActivations->pluck('total')),
                backgroundColor: '#36b9cc'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    /* -----------------------------
     | MODEL DISTRIBUTION CHART
     * ----------------------------- */
    new Chart(document.getElementById('modelChart'), {
        type: 'pie',
        data: {
            labels: @json($modelCounts->pluck('model')),
            datasets: [{
                data: @json($modelCounts->pluck('total')),
                backgroundColor: [
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b',
                    '#858796'
                ]
            }]
        },
        options: { responsive: true }
    });

    /* -----------------------------
     | ACTIVATION TIMELINE CHART
     * ----------------------------- */
    new Chart(document.getElementById('timelineChart'), {
        type: 'line',
        data: {
            labels: @json($activationTimeline->pluck('date')),
            datasets: [{
                label: 'Activations',
                data: @json($activationTimeline->pluck('total')),
                borderColor: '#1cc88a',
                fill: false,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

});
</script>
@endpush
