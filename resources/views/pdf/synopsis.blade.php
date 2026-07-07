<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 40px 46px; }
        * { font-family: "Times New Roman", Times, serif; }
        body { color: #1a1a1a; font-size: 13px; }
        .head { text-align: center; }
        .logo { width: 60px; height: 60px; border: 2px solid #1a1a1a; border-radius: 50%; margin: 0 auto 8px; text-align: center; line-height: 58px; font-weight: bold; font-size: 22px; }
        .cname { font-size: 22px; font-weight: bold; letter-spacing: 1px; }
        .ctag { font-size: 12px; font-style: italic; }
        .caddr { font-size: 11px; color: #333; }
        .rule { border-top: 3px double #1a1a1a; margin: 12px 0 3px; }
        .rule2 { border-top: 1px solid #1a1a1a; margin: 0 0 14px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; letter-spacing: 3px; margin: 6px 0 2px; }
        .refline { text-align: center; font-size: 10px; color: #555; margin-bottom: 16px; }
        .h { font-weight: bold; font-size: 13px; letter-spacing: 1px; background: #f0f0f0; border-left: 4px solid #1a1a1a; padding: 5px 10px; margin: 16px 0 6px; text-transform: uppercase; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td, table.info th { border: 1px solid #bbb; padding: 6px 10px; text-align: left; vertical-align: top; }
        table.info td.k, table.info th { background: #f7f7f7; font-weight: bold; width: 32%; }
        .abstract { text-align: justify; line-height: 1.7; border: 1px solid #bbb; padding: 10px 12px; }
        .sign { width: 100%; margin-top: 55px; }
        .sign td { text-align: center; font-size: 12px; padding-top: 4px; }
        .line { border-top: 1px solid #1a1a1a; width: 150px; margin: 0 auto 4px; }
        .foot { text-align: center; font-size: 10px; color: #666; margin-top: 22px; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="head">
        @php $logo = \App\Models\CollegeSetting::logoDataUri(); @endphp
        @if($logo)
            <img src="{{ $logo }}" alt="logo" style="max-height:64px;max-width:140px;margin-bottom:6px;">
        @else
            <div class="logo">{{ strtoupper(substr($college['name'], 0, 1)) }}</div>
        @endif
        <div class="cname">{{ strtoupper($college['name']) }}</div>
        <div class="ctag">{{ $college['tagline'] }}</div>
        <div class="caddr">{{ $college['address'] }}</div>
        <div class="caddr">{{ $college['affiliation'] }}</div>
    </div>
    <div class="rule"></div>
    <div class="rule2"></div>
    <div class="title">PROJECT SYNOPSIS</div>
    <div class="refline">Ref. No: SPMS/{{ str_pad((string) $project->id, 4, '0', STR_PAD_LEFT) }}/{{ date('Y') }} &nbsp;|&nbsp; Date: {{ $generatedAt }}</div>

    <div class="h">Synopsis Details</div>
    <table class="info">
        <tr><td class="k">Project Title</td><td>{{ $project->name }}</td></tr>
        <tr><td class="k">Type</td><td>{{ ucfirst($project->project_type) }}</td></tr>
        <tr><td class="k">Department / Course</td><td>{{ $project->department?->name ?? '—' }}</td></tr>
        <tr><td class="k">Frontend Technology</td><td>{{ $project->frontend_tech }}</td></tr>
        <tr><td class="k">Backend Technology</td><td>{{ $project->backend_tech }}</td></tr>
        <tr><td class="k">Guide / Faculty</td><td>{{ $project->assignment?->faculty?->user?->name ?? 'Not assigned yet' }}</td></tr>
        <tr><td class="k">Status</td><td>{{ $project->status }}</td></tr>
    </table>

    <div class="h">Team Members</div>
    <table class="info">
        <tr><th style="width:34%">Name</th><th style="width:33%">Roll No</th><th style="width:33%">Role</th></tr>
        @foreach($project->members as $m)
            <tr>
                <td>{{ $m->student?->user?->name ?? '—' }}</td>
                <td>{{ $m->student?->roll_no ?? '—' }}</td>
                <td>{{ ucfirst($m->role_in_project) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="h">Abstract / Synopsis</div>
    <div class="abstract">{{ $project->abstract }}</div>

    <table class="sign">
        <tr>
            <td><div class="line"></div>Signature of Student</td>
            <td><div class="line"></div>Signature of Guide</td>
        </tr>
    </table>

    <div class="foot">{{ $college['name'] }} &middot; {{ $college['website'] }} &middot; Generated {{ $generatedAt }}</div>
</body>
</html>
