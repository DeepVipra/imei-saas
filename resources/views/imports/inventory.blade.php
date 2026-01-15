@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 700px;">
    <h3 class="mb-4">Upload Inventory File</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('imports.inventory.upload') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">
                Inventory Excel File
            </label>

            <input
                type="file"
                name="file"
                class="form-control"
                accept=".xlsx,.xls,.csv"
                required
            >
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                Upload & Process
            </button>
        </div>
    </form>

    <div class="mt-4 text-muted small">
        • File will be processed in background<br>
        • You will see live progress on next screen
    </div>
</div>
@endsection
