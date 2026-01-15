@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Device Report</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="in_stock" @selected(request('status')=='in_stock')>In Stock</option>
                <option value="allocated" @selected(request('status')=='allocated')>Allocated</option>
                <option value="activated" @selected(request('status')=='activated')>Activated</option>
            </select>
        </div>

        <div class="col-md-3">
            <input type="text" name="model" class="form-control"
                   placeholder="Model" value="{{ request('model') }}">
        </div>

        @if(auth()->user()->role === 'admin')
        <div class="col-md-3">
            <select name="dealer_id" class="form-control">
                <option value="">All Dealers</option>
                @foreach($dealers as $dealer)
                    <option value="{{ $dealer->id }}" @selected(request('dealer_id')==$dealer->id)>
                        {{ $dealer->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-3">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Model</th>
                <th>IMEI 1</th>
                <th>IMEI 2</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($devices as $device)
            <tr>
                <td>{{ $device->model }}</td>
                <td>{{ $device->masked_imei_1 }}</td>
                <td>{{ $device->masked_imei_2 }}</td>
                <td>{{ ucfirst($device->status) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $devices->links() }}
</div>
@endsection
