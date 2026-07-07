<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1e293b; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 2px; }
        .muted { color: #64748b; font-size: 11px; }
        h2 { font-size: 14px; margin: 18px 0 6px; border-bottom: 2px solid #0d6efd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; width: 30%; }
        .header { border-bottom: 3px solid #0d6efd; padding-bottom: 10px; margin-bottom: 16px; }
        .badge { background: #e2e8f0; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .abstract { border: 1px solid #cbd5e1; padding: 10px; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <div class="muted">Project Report &middot; Generated on {{ $generatedAt }}</div>
    </div>

    <h2>{{ $project->name }}</h2>
    <table>
        <tr><th>Status</th><td>{{ $project->status }}</td></tr>
        <tr><th>Type</th><td>{{ ucfirst($project->project_type) }}</td></tr>
        <tr><th>Department</th><td>{{ $project->department?->name ?? '—' }}</td></tr>
        <tr><th>Frontend Technology</th><td>{{ $project->frontend_tech }}</td></tr>
        <tr><th>Backend Technology</th><td>{{ $project->backend_tech }}</td></tr>
        <tr><th>Assigned Faculty</th><td>{{ $project->assignment?->faculty?->user?->name ?? 'Not assigned' }}</td></tr>
        <tr><th>Marks</th><td>{{ $project->marks !== null ? $project->marks . ' / 100' : '—' }}</td></tr>
    </table>

    <h2>Team Members</h2>
    <table>
        <thead><tr><th style="width:auto">Name</th><th>Roll No</th><th>Role</th></tr></thead>
        <tbody>
            @foreach($project->members as $member)
                <tr>
                    <td>{{ $member->student?->user?->name ?? '—' }}</td>
                    <td>{{ $member->student?->roll_no ?? '—' }}</td>
                    <td>{{ ucfirst($member->role_in_project) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Abstract / Synopsis</h2>
    <div class="abstract">{{ $project->abstract }}</div>

    @if($project->final_remarks)
        <h2>Final Remarks</h2>
        <div class="abstract">{{ $project->final_remarks }}</div>
    @endif

    @if($project->reviews->isNotEmpty())
        <h2>Review History</h2>
        <table>
            <thead><tr><th style="width:auto">Stage</th><th>Action</th><th>Comments</th></tr></thead>
            <tbody>
                @foreach($project->reviews as $review)
                    <tr>
                        <td>{{ ucfirst($review->stage) }}</td>
                        <td>{{ ucfirst($review->action) }}</td>
                        <td>{{ $review->comments ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
