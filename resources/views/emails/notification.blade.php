<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f9; margin: 0; padding: 24px; color: #1e293b; }
        .card { max-width: 560px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        .head { background: #0d6efd; color: #fff; padding: 18px 24px; font-size: 18px; font-weight: bold; }
        .body { padding: 24px; line-height: 1.6; }
        .btn { display: inline-block; margin-top: 16px; background: #0d6efd; color: #fff !important; text-decoration: none; padding: 10px 20px; border-radius: 6px; }
        .foot { padding: 16px 24px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">{{ config('app.name') }}</div>
        <div class="body">
            <p>Hello{{ $recipientName ? ' ' . $recipientName : '' }},</p>
            <p><strong>{{ $subjectLine }}</strong></p>
            <p>{{ $body }}</p>
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="btn">View Details</a>
            @endif
        </div>
        <div class="foot">
            This is an automated message from {{ config('app.name') }}. Please do not reply.
        </div>
    </div>
</body>
</html>
