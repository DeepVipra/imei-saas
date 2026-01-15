@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Admin Dashboard</h2>
</div>
@endsection


@section('content')
<div class="container">
    <h2>Dealer Dashboard</h2>

    <div class="row mb-4">
        <div class="col">Allocated: <strong>{{ $totalAllocated }}</strong></div>
        <div class="col">Activated: <strong>{{ $activated }}</strong></div>
        <div class="col">Pending: <strong>{{ $pending }}</strong></div>
    </div>

    <h4>City-wise Activations</h4>
    <canvas id="cityChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('cityChart'), {
    type: 'bar',
    data: {
        labels: @json($cityWise->pluck('city')),
        datasets: [{
            label: 'Activations',
            data: @json($cityWise->pluck('total'))
        }]
    }
});
</script>
@endsection
