<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 36px 54px 36px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1e293b; font-size: 11px; margin: 0; }

        /* ---------- Letterhead ---------- */
        .lh { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .lh td { vertical-align: middle; padding: 0; }
        .lh .logo-cell { width: 74px; text-align: center; }
        .lh .logo { width: 60px; height: 60px; border: 2px solid #0d3b66; border-radius: 50%;
            display: inline-block; line-height: 58px; font-weight: bold; font-size: 26px; color: #0d3b66; }
        .cname { font-size: 21px; font-weight: bold; letter-spacing: .5px; color: #0d3b66; }
        .ctag { font-size: 11px; font-style: italic; color: #475569; }
        .caddr { font-size: 10px; color: #64748b; }
        .rule { border-top: 3px solid #0d3b66; margin-top: 7px; }
        .rule2 { border-top: 1px solid #0d3b66; margin-top: 2px; margin-bottom: 12px; }

        /* ---------- Document title ---------- */
        .doc-title { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 2px;
            color: #0d3b66; text-transform: uppercase; margin: 2px 0; }
        .doc-meta { text-align: center; font-size: 10px; color: #64748b; margin-bottom: 12px;
            border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; }

        /* ---------- Summary strip ---------- */
        .summary { background: #eef2f7; border: 1px solid #d0d7e2; border-radius: 4px;
            padding: 7px 12px; font-size: 10.5px; color: #334155; margin-bottom: 12px; }
        .summary strong { color: #0d3b66; }

        /* ---------- Section heading ---------- */
        h2 { font-size: 12px; color: #0d3b66; background: #eef2f7; border-left: 4px solid #0d3b66;
            padding: 5px 9px; margin: 15px 0 6px; text-transform: uppercase; letter-spacing: .4px; }

        /* ---------- Data tables ---------- */
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; page-break-inside: auto; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        table.data th { background: #0d3b66; color: #fff; font-size: 9.5px; text-align: left;
            padding: 6px 8px; border: 1px solid #0d3b66; text-transform: uppercase; }
        table.data td { border: 1px solid #d0d7e2; padding: 5px 8px; vertical-align: top; }
        table.data tbody tr:nth-child(even) { background: #f5f8fc; }
        table.data td.c, table.data th.c { text-align: center; }

        /* Key/value meta table for a single record */
        table.kv { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.kv td, table.kv th { border: 1px solid #d0d7e2; padding: 5px 9px; text-align: left; font-size: 10.5px; }
        table.kv th { background: #f1f5f9; color: #0d3b66; width: 16%; }

        .badge { display: inline-block; padding: 1px 7px; border-radius: 8px; font-size: 8.5px; font-weight: bold; }
        .b-green { background: #d1f0dd; color: #146c43; }
        .b-gray { background: #e2e8f0; color: #475569; }
        .b-amber { background: #ffe8c2; color: #9a6700; }
        .muted { color: #94a3b8; }

        /* ---------- Signatures ---------- */
        .sign { width: 100%; margin-top: 42px; border-collapse: collapse; }
        .sign td { text-align: center; font-size: 10.5px; color: #334155; padding-top: 4px; }
        .sign .line { border-top: 1px solid #334155; width: 140px; margin: 0 auto 4px; }

        .foot { text-align: center; font-size: 9px; color: #64748b; margin-top: 20px;
            border-top: 1px solid #cbd5e1; padding-top: 6px; }
    </style>
</head>
<body>
    @php $logo = \App\Models\CollegeSetting::logoDataUri(); @endphp

    <table class="lh">
        <tr>
            <td class="logo-cell">
                @if($logo)
                    <img src="{{ $logo }}" alt="logo" style="max-height:62px;max-width:70px;">
                @else
                    <span class="logo">{{ strtoupper(substr($college['name'], 0, 1)) }}</span>
                @endif
            </td>
            <td>
                <div class="cname">{{ strtoupper($college['name']) }}</div>
                <div class="ctag">{{ $college['tagline'] }}</div>
                <div class="caddr">{{ $college['address'] }}@if(!empty($college['affiliation'])) &middot; {{ $college['affiliation'] }}@endif</div>
            </td>
        </tr>
    </table>
    <div class="rule"></div>
    <div class="rule2"></div>

    <div class="doc-title">@yield('doc-title')</div>
    <div class="doc-meta">Ref: SPMS/@yield('ref', 'RPT')/{{ date('Y') }} &nbsp;|&nbsp; Generated on {{ $generatedAt }}</div>

    @yield('body')

    @hasSection('signatures')
        <table class="sign">
            <tr>@yield('signatures')</tr>
        </table>
    @endif

    <div class="foot">{{ $college['name'] }} &middot; {{ $college['phone'] }} &middot; {{ $college['email'] }} &middot; {{ $college['website'] }}</div>
</body>
</html>
