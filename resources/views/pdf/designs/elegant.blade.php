<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #1e293b; font-size: 12px; }
        .outer { border: 8px solid #0b1f3a; margin: 14px; padding: 0; }
        .inner { border: 2px solid #c8a44d; margin: 6px; padding: 22px 26px; }
        .top { text-align: center; border-bottom: 2px solid #c8a44d; padding-bottom: 12px; margin-bottom: 4px; }
        .crest { width: 56px; height: 56px; background: #0b1f3a; color: #c8a44d; border-radius: 50%; margin: 0 auto 6px; text-align: center; line-height: 56px; font-size: 22px; font-weight: bold; border: 2px solid #c8a44d; }
        .cname { font-size: 21px; font-weight: bold; color: #0b1f3a; letter-spacing: 1px; }
        .ctag { font-size: 11px; color: #c8a44d; font-weight: bold; letter-spacing: 1px; }
        .caddr { font-size: 10px; color: #64748b; }
        .strip { background: #0b1f3a; color: #fff; text-align: center; padding: 8px; letter-spacing: 5px; font-size: 14px; margin: 14px 0; border-radius: 3px; }
        .strip span { color: #c8a44d; }
        table.info { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.info td { border: 1px solid #e2d4ac; padding: 7px 10px; }
        table.info td.k { background: #faf6ea; font-weight: bold; width: 32%; color: #0b1f3a; }
        .h { color: #0b1f3a; font-weight: bold; font-size: 12px; letter-spacing: 1px; border-bottom: 2px solid #c8a44d; padding-bottom: 4px; margin: 16px 0 8px; text-transform: uppercase; }
        .box { border: 1px solid #e2d4ac; background: #faf6ea; padding: 10px 12px; line-height: 1.6; border-radius: 3px; }
        .marks { display: inline-block; background: #0b1f3a; color: #c8a44d; padding: 3px 12px; border-radius: 12px; font-weight: bold; }
        .sign { width: 100%; margin-top: 46px; }
        .sign td { text-align: center; font-size: 11px; color: #0b1f3a; }
        .line { border-top: 1px solid #0b1f3a; width: 150px; margin: 0 auto 4px; }
        .foot { text-align: center; font-size: 10px; color: #94a3b8; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="outer">
        <div class="inner">
            <div class="top">
                <div class="crest">{{ strtoupper(substr($college['name'], 0, 1)) }}</div>
                <div class="cname">{{ $college['name'] }}</div>
                <div class="ctag">{{ strtoupper($college['tagline']) }}</div>
                <div class="caddr">{{ $college['address'] }} &middot; {{ $college['affiliation'] }}</div>
            </div>

            <div class="strip">P R O J E C T &nbsp;&nbsp; <span>R E P O R T</span></div>

            <table class="info">
                <tr><td class="k">Project Title</td><td>{{ $project->name }}</td></tr>
                <tr><td class="k">Type</td><td>{{ ucfirst($project->project_type) }}</td></tr>
                <tr><td class="k">Department / Course</td><td>{{ $project->department?->name ?? '—' }}</td></tr>
                <tr><td class="k">Technologies</td><td>{{ $project->frontend_tech }} &amp; {{ $project->backend_tech }}</td></tr>
                <tr><td class="k">Guide / Faculty</td><td>{{ $project->assignment?->faculty?->user?->name ?? 'Not assigned' }}</td></tr>
                <tr><td class="k">Status</td><td>{{ $project->status }}</td></tr>
                <tr><td class="k">Marks Awarded</td><td>@if($project->marks !== null)<span class="marks">{{ $project->marks }} / 100</span>@else — @endif</td></tr>
            </table>

            <div class="h">Team Members</div>
            <table class="info">
                @foreach($project->members as $m)
                    <tr>
                        <td class="k">{{ ucfirst($m->role_in_project) }}</td>
                        <td>{{ $m->student?->user?->name ?? '—' }} ({{ $m->student?->roll_no ?? '—' }})</td>
                    </tr>
                @endforeach
            </table>

            <div class="h">Abstract / Synopsis</div>
            <div class="box">{{ $project->abstract }}</div>

            @if($project->final_remarks)
                <div class="h">Final Remarks</div>
                <div class="box">{{ $project->final_remarks }}</div>
            @endif

            <table class="sign">
                <tr>
                    <td><div class="line"></div>Guide / Faculty</td>
                    <td><div class="line"></div>Head of Department</td>
                    <td><div class="line"></div>Principal</td>
                </tr>
            </table>

            <div class="foot">{{ $college['name'] }} &middot; {{ $college['website'] }} &middot; Generated on {{ $generatedAt }}</div>
        </div>
    </div>
</body>
</html>
