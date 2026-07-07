@extends('layouts.app')
@section('title', 'Import Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Students</h3>
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to list</a>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label">CSV or Excel file</label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx,.xls" required>
                    <div class="form-text">Accepted: .csv, .xlsx, .xls — max 5&nbsp;MB.</div>
                    <button class="btn btn-primary mt-3"><i class="bi bi-upload"></i> Upload &amp; Import</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2">Required columns (header row)</h6>
                <p class="text-muted small mb-2">The first row must contain these column names. <code>department</code> is matched against an existing department name or code. New students are created as <strong>active</strong> with the role <strong>student</strong>.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light">
                            <tr><th>name</th><th>email</th><th>roll_no</th><th>department</th><th>semester</th><th>phone</th><th>password</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Rahul Sharma</td><td>rahul@x.com</td><td>MSC2026010</td><td>MSC (IT &amp; CA)</td><td>3</td><td>9000000000</td><td>secret123</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('import_failures') !== null)
    @php $failures = session('import_failures'); $total = session('import_total', 0); @endphp
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="mb-3">
                Import summary —
                <span class="text-success">{{ $total - count($failures) }} imported</span>,
                <span class="{{ count($failures) ? 'text-danger' : 'text-muted' }}">{{ count($failures) }} failed</span>
                <span class="text-muted">of {{ $total }} row(s)</span>
            </h6>
            @if(count($failures))
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr><th>Row</th><th>Roll No</th><th>Email</th><th>Problem(s)</th></tr>
                        </thead>
                        <tbody>
                        @foreach($failures as $f)
                            <tr>
                                <td>{{ $f['line'] }}</td>
                                <td>{{ $f['roll_no'] ?: '—' }}</td>
                                <td>{{ $f['email'] ?: '—' }}</td>
                                <td>
                                    <ul class="mb-0 small text-danger">
                                        @foreach($f['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-success mb-0"><i class="bi bi-check-circle"></i> All rows imported successfully.</p>
            @endif
        </div>
    </div>
@endif
@endsection
