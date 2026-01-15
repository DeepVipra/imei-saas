@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Upload Sales to Dealer File</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        id="uploadForm"
        method="POST"
        action="{{ route('imports.allocation.upload') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="mb-3">
            <label class="form-label">Excel File</label>
            <input
                type="file"
                name="file"
                class="form-control"
                accept=".xlsx,.xls,.csv"
                required
            >
        </div>

        {{-- Progress Bar --}}
        <div class="progress mb-3 d-none" id="progressWrapper">
            <div
                class="progress-bar progress-bar-striped progress-bar-animated"
                id="progressBar"
                role="progressbar"
                style="width: 0%"
            >
                0%
            </div>
        </div>

        <button type="submit" class="btn btn-warning" id="submitBtn">
            Upload
        </button>
    </form>

    <p class="mt-3 text-muted">
        Dealer name in the Excel file must exactly match an existing dealer record.
    </p>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('uploadForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const progressWrapper = document.getElementById('progressWrapper');
    const progressBar = document.getElementById('progressBar');
    const submitBtn = document.getElementById('submitBtn');

    progressWrapper.classList.remove('d-none');
    submitBtn.disabled = true;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

    xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerText = percent + '%';
        }
    });

    xhr.onload = function () {
        if (xhr.status === 200) {
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-success');
            progressBar.innerText = 'Upload Complete';

            // Controller redirects to progress view
            window.location.href = xhr.responseURL;
        } else {
            progressBar.classList.add('bg-danger');
            progressBar.innerText = 'Upload Failed';
            submitBtn.disabled = false;
        }
    };

    xhr.onerror = function () {
        progressBar.classList.add('bg-danger');
        progressBar.innerText = 'Error';
        submitBtn.disabled = false;
    };

    xhr.send(formData);
});
</script>
@endpush
