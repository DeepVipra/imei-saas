@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Upload Activation File</h2>

    <form method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" class="form-control mb-3" required>
        <button class="btn btn-danger">Upload</button>
    </form>
</div>
@endsection
