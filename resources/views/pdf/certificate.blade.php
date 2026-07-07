<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; }
        .frame {
            margin: 25px;
            border: 6px double #0d6efd;
            padding: 50px 40px;
            text-align: center;
            height: 480px;
        }
        .org { font-size: 14px; letter-spacing: 2px; color: #64748b; text-transform: uppercase; }
        .title { font-size: 40px; color: #0d6efd; margin: 20px 0 6px; font-weight: bold; }
        .sub { font-size: 14px; color: #64748b; margin-bottom: 30px; }
        .name { font-size: 28px; font-weight: bold; margin: 10px 0; border-bottom: 2px solid #1e293b; display: inline-block; padding: 0 30px 6px; }
        .body { font-size: 14px; color: #334155; line-height: 1.7; margin: 20px auto; width: 80%; }
        .project { font-weight: bold; color: #0d6efd; }
        .marks { font-size: 20px; margin-top: 16px; }
        .footer { margin-top: 40px; }
        .sign { display: inline-block; width: 200px; border-top: 1px solid #334155; padding-top: 6px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="frame">
        @php $logo = \App\Models\CollegeSetting::logoDataUri(); @endphp
        @if($logo)
            <img src="{{ $logo }}" alt="logo" style="max-height:60px;max-width:130px;margin-bottom:6px;">
        @endif
        <div class="org">{{ $college['name'] ?? config('app.name') }}</div>
        @if(!empty($college['tagline']))
            <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $college['tagline'] }}</div>
        @endif
        <div class="title">Certificate of Completion</div>
        <div class="sub">This is proudly presented to</div>

        @foreach($project->members as $member)
            <div class="name">{{ $member->student?->user?->name ?? '—' }}</div><br>
        @endforeach

        <div class="body">
            for successfully completing the project
            <span class="project">&ldquo;{{ $project->name }}&rdquo;</span>
            built with {{ $project->frontend_tech }} and {{ $project->backend_tech }},
            under the guidance of {{ $project->assignment?->faculty?->user?->name ?? 'the department' }}.
        </div>

        <div class="marks">Grade Awarded: <strong>{{ $project->marks }}/100</strong></div>

        <div class="footer">
            <span class="sign">{{ $project->assignment?->faculty?->user?->name ?? 'Faculty' }}<br>Guide</span>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="sign">Date: {{ $generatedAt }}</span>
        </div>
    </div>
</body>
</html>
