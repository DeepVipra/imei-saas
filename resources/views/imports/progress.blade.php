@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 700px;">
    <h3 class="mb-4">Import Progress</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <p class="mb-3">
                <strong>File:</strong> {{ $importFile->original_filename }}<br>
                <strong>Status:</strong>
                <span id="statusText" class="badge bg-secondary">
                    {{ ucfirst($importFile->status) }}
                </span>
            </p>

            <div class="progress mb-3" style="height: 25px;">
                <div
                    id="progressBar"
                    class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar"
                    style="width: 0%;">
                    0%
                </div>
            </div>

            <p class="text-muted mb-0" id="progressText">
                Initializing import…
            </p>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const importId = {{ $importFile->id }};
    const POLL_INTERVAL = 2000;

    const progressBar  = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const statusText   = document.getElementById('statusText');

    let poller = null;
    let finalized = false; // 🔒 FIX 3: UI FINALIZATION LOCK

    function setBadge(status) {
        statusText.className = 'badge';

        switch (status) {
            case 'completed':
                statusText.classList.add('bg-success');
                break;
            case 'failed':
                statusText.classList.add('bg-danger');
                break;
            case 'processing':
                statusText.classList.add('bg-primary');
                break;
            default:
                statusText.classList.add('bg-secondary');
        }

        statusText.innerText =
            status.charAt(0).toUpperCase() + status.slice(1);
    }

    async function pollProgress() {
        if (finalized) return;

        try {
            const response = await fetch(`/imports/${importId}/progress`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) return;

            const data = await response.json();

            const status    = data.status ?? 'queued';
            const total     = Number.isInteger(data.total) ? data.total : null;
            const processed = Number(data.processed ?? 0);

            setBadge(status);

            /* -------------------------------------------------------------
             | SAFE PROGRESS CALCULATION (FIX 3 CORE)
             * ----------------------------------------------------------- */
            let percent = 0;

            if (total !== null && total > 0) {
                percent = Math.min(
                    99, // ⛔ NEVER show 100% until backend confirms
                    Math.floor((processed / total) * 100)
                );
            }

            progressBar.style.width = percent + '%';
            progressBar.innerText   = percent + '%';

            /* -------------------------------------------------------------
             | COMPLETION GUARD (BACKEND-TRUTH-ONLY)
             * ----------------------------------------------------------- */
            if (
                status === 'completed' &&
                total !== null &&
                processed >= total
            ) {
                finalized = true;
                clearInterval(poller);

                progressBar.style.width = '100%';
                progressBar.innerText   = '100%';
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');

                progressText.innerText =
                    'Import completed successfully. Redirecting…';

                setTimeout(() => {
                    window.location.href = "{{ route('dashboard') }}";
                }, 2500);

                return;
            }

            /* -------------------------------------------------------------
             | FAILURE
             * ----------------------------------------------------------- */
            if (status === 'failed') {
                finalized = true;
                clearInterval(poller);

                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');

                progressText.innerText =
                    'Import failed. Please review the error log.';

                return;
            }

            /* -------------------------------------------------------------
             | STATUS MESSAGES
             * ----------------------------------------------------------- */
            if (status === 'queued') {
                progressText.innerText =
                    'Import queued. Waiting for worker…';
            } else if (status === 'processing' && total === null) {
                progressText.innerText =
                    'Preparing rows for processing…';
            } else {
                progressText.innerText =
                    `Processed ${processed} of ${total} rows…`;
            }

        } catch (err) {
            console.error('Progress polling failed:', err);
        }
    }

    // Delayed start avoids race with job pickup
    setTimeout(() => {
        pollProgress();
        poller = setInterval(pollProgress, POLL_INTERVAL);
    }, 1000);

});
</script>
@endpush
