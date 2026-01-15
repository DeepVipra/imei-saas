@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Dealer</h2>

    <form method="POST" action="/dealers" class="mb-4">
        @csrf
        <input type="text" name="name" class="form-control mb-2"
               placeholder="Dealer Name" required>

        <button class="btn btn-primary">Create Dealer</button>
    </form>

    <h4>Existing Dealers</h4>
    <ul class="list-group">
        @foreach($dealers as $dealer)
            <li class="list-group-item">{{ $dealer->name }}</li>
        @endforeach
    </ul>
</div>
@endsection
