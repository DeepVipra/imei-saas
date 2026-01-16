@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Device Report</h2>

    {{-- =========================
     | FILTER FORM + EXPORT
     * ========================= --}}
    <form method="GET" class="row g-3 mb-4 align-items-end">

        {{-- STATUS FILTER --}}
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="in_stock" @selected(request('status')=='in_stock')>
                    In Stock
                </option>
                <option value="allocated" @selected(request('status')=='allocated')>
                    Allocated
                </option>
                <option value="activated" @selected(request('status')=='activated')>
                    Activated
                </option>
            </select>
        </div>

        {{-- MODEL FILTER --}}
        <div class="col-md-3">
            <label class="form-label">Model</label>
            <input type="text"
                   name="model"
                   class="form-control"
                   placeholder="Model"
                   value="{{ request('model') }}">
        </div>

        {{-- DEALER FILTER (ADMIN ONLY) --}}
        @if(auth()->user()->role === 'admin')
            <div class="col-md-3">
                <label class="form-label">Dealer</label>
                <select name="dealer_id" class="form-control">
                    <option value="">All Dealers</option>
                    @foreach($dealers as $dealer)
                        <option value="{{ $dealer->id }}"
                            @selected(request('dealer_id') == $dealer->id)>
                            {{ $dealer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- FILTER BUTTON --}}
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                Filter
            </button>
        </div>

        {{-- EXPORT BUTTON --}}
        <div class="col-md-2">
            <a href="{{ route('reports.devices.export', request()->query()) }}"
               class="btn btn-success w-100">
                Export Excel
            </a>
        </div>

    </form>

    {{-- =========================
     | DEVICES TABLE
     * ========================= --}}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Model</th>
                <th>IMEI 1</th>
                <th>IMEI 2</th>
                <th>Dealer</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($devices as $device)
            <tr>
                <td>{{ $device->model }}</td>
                <td>{{ $device->masked_imei_1 }}</td>
                <td>{{ $device->masked_imei_2 }}</td>
                <td>{{ $device->allocation?->dealer?->name ?? '-' }}</td>
                <td>{{ ucfirst($device->status) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">
                    No records found
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $devices->links() }}
    </div>
</div>
@endsection
