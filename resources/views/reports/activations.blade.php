@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Activation Report</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="date" name="from_date" class="form-control"
                   value="{{ request('from_date') }}">
        </div>

        <div class="col-md-3">
            <input type="date" name="to_date" class="form-control"
                   value="{{ request('to_date') }}">
        </div>

        <div class="col-md-3">
            <input type="text" name="province" class="form-control"
                   placeholder="Province" value="{{ request('province') }}">
        </div>

        <div class="col-md-3">
            <input type="text" name="city" class="form-control"
                   placeholder="City" value="{{ request('city') }}">
        </div>

        <div class="col-md-12">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>IMEI</th>
                <th>Date</th>
                <th>Province</th>
                <th>City</th>
            </tr>
        </thead>
        <tbody>
        @foreach($activations as $activation)
            <tr>
                <td>{{ substr($activation->activated_imei, -4) }}</td>
                <td>{{ $activation->activation_date }}</td>
                <td>{{ $activation->province }}</td>
                <td>{{ $activation->city }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $activations->links() }}
</div>
@endsection
