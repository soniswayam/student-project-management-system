<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $project->name }}</span>
        <span>
            @if(($project->revision ?? 1) > 1)
                <span class="badge bg-light text-dark border me-1" title="This synopsis has been revised and resubmitted">
                    Revision {{ $project->revision }}
                </span>
            @endif
            <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-2"><span class="text-muted small">Type</span><br>
                <span class="badge bg-{{ $project->project_type === 'group' ? 'primary' : 'secondary' }}">{{ ucfirst($project->project_type) }}</span>
            </div>
            <div class="col-md-6 mb-2"><span class="text-muted small">Department</span><br>{{ $project->department?->name ?? '—' }}</div>
            <div class="col-md-6 mb-2"><span class="text-muted small">Frontend Technology</span><br>{{ $project->frontend_tech }}</div>
            <div class="col-md-6 mb-2"><span class="text-muted small">Backend Technology</span><br>{{ $project->backend_tech }}</div>
            <div class="col-md-6 mb-2"><span class="text-muted small">Assigned Faculty</span><br>
                {{ $project->assignment?->faculty?->user?->name ?? 'Not assigned yet' }}
            </div>
            @php $hideResult = auth()->user()->isStudent() && ! $project->isCompleted(); @endphp

            @if($hideResult)
                <div class="col-md-6 mb-2"><span class="text-muted small">Final Result</span><br>
                    <span class="text-muted">Result awaited</span>
                </div>
            @elseif($project->marksLabel())
                <div class="col-md-6 mb-2"><span class="text-muted small">Final Result</span><br>
                    {{ $project->marksLabel() }}
                </div>
            @endif
            <div class="col-12 mb-2"><span class="text-muted small">Abstract / Synopsis</span><br>{{ $project->abstract }}</div>
            @if($project->final_remarks && ! $hideResult)
                <div class="col-12"><span class="text-muted small">Final Remarks</span><br>{{ $project->final_remarks }}</div>
            @endif
        </div>

        <hr>
        <span class="text-muted small">Group</span>
        <ul class="list-group list-group-flush">
            @foreach($project->members as $member)
                <li class="list-group-item px-0">
                    <i class="bi bi-person me-1"></i>
                    {{ $member->student?->user?->name ?? '—' }}
                    <span class="text-muted small">({{ $member->student?->roll_no }})</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
