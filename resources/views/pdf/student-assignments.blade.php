@extends('pdf.layouts.report')
@section('doc-title', 'My Assignments')
@section('ref', 'STU')

@section('body')
    <table class="kv">
        <tr>
            <th style="width:16%">Student</th><td style="width:34%">{{ $student->user?->name ?? '—' }}</td>
            <th style="width:16%">Roll No</th><td>{{ $student->roll_no ?? '—' }}</td>
        </tr>
        <tr>
            <th>Department</th><td>{{ $student->department?->name ?? '—' }}</td>
            <th>Total</th><td>{{ $assignments->count() }} assignment(s)</td>
        </tr>
    </table>

    <table class="data" style="margin-top:10px;">
        <thead>
            <tr>
                <th class="c" style="width:6%">No.</th>
                <th style="width:22%">Subject</th>
                <th class="c" style="width:8%">Asg #</th>
                <th style="width:24%">Title</th>
                <th style="width:11%">Type</th>
                <th style="width:14%">Due Date</th>
                <th class="c">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $i => $a)
                @php
                    $mine = $mySubmissions->get($a->id);
                    $status = ! $mine ? 'Pending' : ($mine->isChecked() ? 'Checked' : ($mine->isLate() ? 'Submitted (Late)' : 'Submitted'));
                    $cls = ! $mine ? 'b-gray' : ($mine->isChecked() ? 'b-green' : ($mine->isLate() ? 'b-amber' : 'b-gray'));
                @endphp
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $a->subject ?? '—' }}</td>
                    <td class="c">{{ $a->assignment_no ?? '—' }}</td>
                    <td>{{ $a->title }}</td>
                    <td>{{ $a->type }}</td>
                    <td>{{ $a->due_date?->format('d M Y') ?? '—' }}</td>
                    <td class="c"><span class="badge {{ $cls }}">{{ $status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="c muted">No assignments posted for your department yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
