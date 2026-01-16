@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Admin Dashboard</h2>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('dashboard') }}" class="card p-3 mb-4">
        <div class="row g-3">

            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Dealer</label>
                <select name="dealer_id" class="form-select">
                    <option value="">All Dealers</option>
                    @foreach ($dealerStats as $dealer)
                        <option value="{{ $dealer->id }}" {{ request('dealer_id') == $dealer->id ? 'selected' : '' }}>
                            {{ $dealer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Model</label>
                <select name="model" class="form-select">
                    <option value="">All Models</option>
                    @foreach ($modelCounts as $model)
                        <option value="{{ $model->model }}" {{ request('model') == $model->model ? 'selected' : '' }}>
                            {{ $model->model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Province</label>
                <select name="province" class="form-select">
                    <option value="">All Provinces</option>
                    @foreach ($provinceActivations as $province)
                        <option value="{{ $province->province }}" {{ request('province') == $province->province ? 'selected' : '' }}>
                            {{ $province->province }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary w-100">Apply Filters</button>
            </div>

            <div class="col-md-3 align-self-end">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary w-100">Reset</a>
            </div>

        </div>
    </form>

    {{-- KPI CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3"><div class="card p-3 text-center"><h6>Total Devices</h6><strong>{{ $totalDevices }}</strong></div></div>
        <div class="col-md-3"><div class="card p-3 text-center"><h6>Allocated but Not Activated</h6><strong>{{ $allocatedDevices }}</strong></div></div>
        <div class="col-md-3"><div class="card p-3 text-center"><h6>Activated</h6><strong>{{ $activatedDevices }}</strong></div></div>
        <div class="col-md-3"><div class="card p-3 text-center"><h6>In Stock</h6><strong>{{ $inStockDevices }}</strong></div></div>
    </div>

    <h4>Dealer-wise Allocation vs Activation</h4>
    <canvas id="dealerChart" style="min-height:300px"></canvas>

    <h4 class="mt-5">Activated Devices by Province</h4>
    <canvas id="provinceChart" style="min-height:300px"></canvas>

    <h4 class="mt-5">Model Distribution</h4>
    <canvas id="modelChart" style="min-height:300px"></canvas>

    <h4 class="mt-5">Activation Timeline</h4>
    <canvas id="timelineChart" style="min-height:300px"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Dealer-wise Allocation vs Activation
    new Chart(document.getElementById('dealerChart'), {
        type: 'bar',
        data: {
            labels: @json($dealerStats->pluck('name')),
            datasets: [
                {
                    label: 'Allocated',
                    data: @json($dealerStats->pluck('allocated_count')),
                },
                {
                    label: 'Activated',
                    data: @json($dealerStats->pluck('activated_count')),
                }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Province-wise Activation
    new Chart(document.getElementById('provinceChart'), {
        type: 'bar',
        data: {
            labels: @json($provinceActivations->pluck('province')),
            datasets: [{
                label: 'Activated',
                data: @json($provinceActivations->pluck('total')),
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Model Distribution
    new Chart(document.getElementById('modelChart'), {
        type: 'pie',
        data: {
            labels: @json($modelCounts->pluck('model')),
            datasets: [{
                data: @json($modelCounts->pluck('total')),
            }]
        },
        options: { responsive: true }
    });

    // Activation Timeline
    new Chart(document.getElementById('timelineChart'), {
        type: 'line',
        data: {
            labels: @json($activationTimeline->pluck('date')),
            datasets: [{
                label: 'Activations',
                data: @json($activationTimeline->pluck('total')),
                tension: 0.3,
                fill: false
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

});
</script>
@endsection
