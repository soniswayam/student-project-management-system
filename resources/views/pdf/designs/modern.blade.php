<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24px 30px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .banner { background: #0d6efd; color: #fff; padding: 16px 20px; border-radius: 6px; }
        .banner table { width: 100%; }
        .logo { width: 54px; height: 54px; background: rgba(255,255,255,.2); border: 2px solid #fff; border-radius: 8px; text-align: center; line-height: 50px; font-weight: bold; font-size: 18px; }
        .cname { font-size: 20px; font-weight: bold; }
        .ctag { font-size: 11px; opacity: .9; }
        .doc { font-size: 13px; margin-top: 6px; letter-spacing: 2px; }
        .subline { text-align: center; color: #64748b; font-size: 10px; margin: 6px 0 14px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
        .sec { background: #eef4ff; color: #0d6efd; font-weight: bold; padding: 6px 10px; border-left: 4px solid #0d6efd; margin: 16px 0 8px; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td { border: 1px solid #dbe3ee; padding: 7px 10px; }
        table.info td.k { background: #f8fafc; font-weight: bold; width: 32%; color: #475569; }
        .box { border: 1px solid #dbe3ee; background: #f8fafc; border-radius: 4px; padding: 10px 12px; line-height: 1.6; }
        .foot { margin-top: 26px; border-top: 2px solid #e2e8f0; padding-top: 8px; color: #94a3b8; font-size: 10px; }
        .sign { margin-top: 40px; }
        .sign td { text-align: center; color: #475569; font-size: 11px; }
        .line { border-top: 1px solid #334155; width: 160px; margin: 0 auto 4px; }
        .pill { background: #198754; color: #fff; padding: 2px 10px; border-radius: 10px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="banner">
        <table>
            <tr>
                <td style="width:60px"><div class="logo">{{ strtoupper(substr($college['name'], 0, 1)) }}</div></td>
                <td>
                    <div class="cname">{{ $college['name'] }}</div>
                    <div class="ctag">{{ $college['tagline'] }}</div>
                    <div class="doc">PROJECT REPORT</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="subline">{{ $college['address'] }} &middot; {{ $college['affiliation'] }} &middot; {{ $college['email'] }}</div>

    <div class="sec">Project Details</div>
    <table class="info">
        <tr><td class="k">Project Title</td><td>{{ $project->name }}</td></tr>
        <tr><td class="k">Type</td><td>{{ ucfirst($project->project_type) }}</td></tr>
        <tr><td class="k">Department / Course</td><td>{{ $project->department?->name ?? '—' }}</td></tr>
        <tr><td class="k">Frontend Technology</td><td>{{ $project->frontend_tech }}</td></tr>
        <tr><td class="k">Backend Technology</td><td>{{ $project->backend_tech }}</td></tr>
        <tr><td class="k">Guide / Faculty</td><td>{{ $project->assignment?->faculty?->user?->name ?? 'Not assigned' }}</td></tr>
        <tr><td class="k">Status</td><td>{{ $project->status }}</td></tr>
        <tr><td class="k">Marks</td><td>@if($project->marks !== null)<span class="pill">{{ $project->marks }} / 100</span>@else — @endif</td></tr>
    </table>

    <div class="sec">Team Members</div>
    <table class="info">
        <tr><td class="k">Name</td><td class="k" style="width:auto">Roll No</td><td class="k" style="width:20%">Role</td></tr>
        @foreach($project->members as $m)
            <tr>
                <td>{{ $m->student?->user?->name ?? '—' }}</td>
                <td>{{ $m->student?->roll_no ?? '—' }}</td>
                <td>{{ ucfirst($m->role_in_project) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="sec">Abstract / Synopsis</div>
    <div class="box">{{ $project->abstract }}</div>

    @if($project->final_remarks)
        <div class="sec">Final Remarks</div>
        <div class="box">{{ $project->final_remarks }}</div>
    @endif

    <table class="sign">
        <tr>
            <td><div class="line"></div>Guide / Faculty</td>
            <td><div class="line"></div>Head of Department</td>
            <td><div class="line"></div>Principal</td>
        </tr>
    </table>

    <div class="foot">
        {{ $college['name'] }} &middot; {{ $college['website'] }} &middot; Generated on {{ $generatedAt }}
    </div>
</body>
</html>
