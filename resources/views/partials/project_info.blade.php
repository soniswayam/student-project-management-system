{{-- Expects: $project (with leader.user, members.student.user, department, assignment.faculty.user) --}}
<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $project->name }}</span>
        <span class="badge bg-{{ $project->statusColor() }}">{{ $project->status }}</span>
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
            <div class="col-md-6 mb-2"><span class="text-muted small">Final Result</span><br>
                @if($hideResult)
                    <span class="text-muted">Result awaited</span>
                @else
                    {{ $project->marks !== null ? $project->marks . ' / 100' : '—' }}
                @endif
            </div>
            <div class="col-12 mb-2"><span class="text-muted small">Abstract / Synopsis</span><br>{{ $project->abstract }}</div>
            @if($project->final_remarks && ! $hideResult)
                <div class="col-12"><span class="text-muted small">Final Remarks</span><br>{{ $project->final_remarks }}</div>
            @endif
        </div>

        <hr>
        <span class="text-muted small">Members</span>
        <ul class="list-group list-group-flush">
            @foreach($project->members as $member)
                <li class="list-group-item px-0 d-flex justify-content-between">
                    <span>
                        <i class="bi bi-person me-1"></i>
                        {{ $member->student?->user?->name ?? '—' }}
                        <span class="text-muted small">({{ $member->student?->roll_no }})</span>
                    </span>
                    <span class="badge bg-{{ $member->role_in_project === 'leader' ? 'dark' : 'secondary' }}">{{ ucfirst($member->role_in_project) }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
