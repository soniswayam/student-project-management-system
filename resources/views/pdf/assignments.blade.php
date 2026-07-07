@extends('pdf.layouts.report')
@section('doc-title', 'Assignments & Submissions Report')
@section('ref', 'ASG')

@section('signatures')
    <td><div class="line"></div>Subject Faculty</td>
    <td><div class="line"></div>Head of Department</td>
    <td><div class="line"></div>Principal</td>
@endsection

@section('body')
    @php
        $totalAssignments = $assignments->count();
        $totalSubmissions = $assignments->sum(fn ($a) => $a->submissions->count());
        $totalChecked = $assignments->sum(fn ($a) => $a->submissions->where('status', 'checked')->count());
    @endphp

    <div class="summary">
        <strong>Total assignments:</strong> {{ $totalAssignments }} &nbsp;&middot;&nbsp;
        <strong>Total submissions:</strong> {{ $totalSubmissions }} &nbsp;&middot;&nbsp;
        <strong>Checked:</strong> {{ $totalChecked }} &nbsp;&middot;&nbsp;
        <strong>Pending review:</strong> {{ $totalSubmissions - $totalChecked }}
    </div>

    @forelse($assignments as $assignment)
        <h2>{{ $loop->iteration }}. {{ $assignment->title }}</h2>

        <table class="kv">
            <tr>
                <th>Department</th><td style="width:34%">{{ $assignment->department?->name ?? '—' }}</td>
                <th>Faculty</th><td style="width:34%">{{ $assignment->faculty?->user?->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Due Date</th><td>{{ $assignment->due_date?->format('d M Y, H:i') ?? 'No due date' }}</td>
                <th>Submitted</th><td>{{ $assignment->submissions->count() }} of {{ $assignment->department?->students()->count() ?? 0 }} students</td>
            </tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th class="c" style="width:5%">#</th>
                    <th style="width:26%">Student Name</th>
                    <th style="width:14%">Roll No</th>
                    <th style="width:20%">Submitted On</th>
                    <th class="c" style="width:12%">Status</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignment->submissions as $s)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $s->student?->user?->name ?? '—' }}</td>
                        <td>{{ $s->student?->roll_no ?? '—' }}</td>
                        <td>
                            {{ $s->submitted_at?->format('d M Y, H:i') ?? '—' }}
                            @if($s->isLate())<span class="badge b-amber">Late</span>@endif
                        </td>
                        <td class="c">
                            @if($s->status === 'checked')
                                <span class="badge b-green">Checked</span>
                            @else
                                <span class="badge b-gray">Submitted</span>
                            @endif
                        </td>
                        <td>{{ $s->feedback ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="c muted">No students have submitted this assignment yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    @empty
        <p class="muted">No assignments found.</p>
    @endforelse
@endsection
