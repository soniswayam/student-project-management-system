@extends('pdf.layouts.report')
@section('doc-title', 'Assignment Distribution Report')
@section('ref', 'DST')

@section('signatures')
    <td><div class="line"></div>Subject Faculty</td>
    <td><div class="line"></div>Head of Department</td>
    <td><div class="line"></div>Principal</td>
@endsection

@section('body')
    <div class="summary">
        <strong>Faculty:</strong> {{ $totals['faculty'] }} &nbsp;·&nbsp;
        <strong>Subjects:</strong> {{ $totals['subjects'] }} &nbsp;·&nbsp;
        <strong>Assignments:</strong> {{ $totals['assignments'] }} &nbsp;·&nbsp;
        <strong>Submissions:</strong> {{ $totals['submissions'] }} &nbsp;·&nbsp;
        <strong>Checked:</strong> {{ $totals['checked'] }}
    </div>

    @forelse($byFaculty as $facultyName => $assignments)
        <h2>Faculty: {{ $facultyName }}</h2>

        @foreach($assignments->groupBy('subject') as $subject => $subjectAssignments)
            <div style="font-weight:bold; color:#0d3b66; margin:10px 0 3px; border-bottom:1px solid #0d3b66; padding-bottom:2px;">
                Subject: {{ $subject ?: '—' }}
            </div>

            @foreach($subjectAssignments as $a)
                @php $total = $studentCounts[$a->department_id] ?? 0; @endphp
                <table class="kv" style="margin-top:6px;">
                    <tr>
                        <th style="width:14%">Assignment</th>
                        <td style="width:44%">{{ $a->assignment_no ? 'No. '.$a->assignment_no.' — ' : '' }}{{ $a->title }}</td>
                        <th style="width:12%">Type</th>
                        <td>{{ $a->type }}</td>
                    </tr>
                    <tr>
                        <th>Due Date</th>
                        <td>{{ $a->due_date?->format('d M Y') ?? '—' }}</td>
                        <th>Submitted</th>
                        <td>{{ $a->submissions_count }} / {{ $total }} &nbsp;·&nbsp; Checked {{ $a->checked_count }}</td>
                    </tr>
                </table>

                <table class="data">
                    <thead>
                        <tr>
                            <th class="c" style="width:6%">#</th>
                            <th style="width:34%">Student Name</th>
                            <th style="width:16%">Roll No</th>
                            <th style="width:26%">Submitted On</th>
                            <th class="c" style="width:18%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($a->submissions as $s)
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
                            </tr>
                        @empty
                            <tr><td colspan="5" class="c muted">No students have submitted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        @endforeach
    @empty
        <p class="muted">No assignments found.</p>
    @endforelse
@endsection
