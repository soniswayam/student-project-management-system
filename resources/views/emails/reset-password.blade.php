<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f9; margin: 0; padding: 24px; color: #1e293b; }
        .card { max-width: 560px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        .head { background: #0d6efd; color: #fff; padding: 18px 24px; font-size: 18px; font-weight: bold; }
        .body { padding: 24px; line-height: 1.6; }
        .btn { display: inline-block; margin: 16px 0; background: #0d6efd; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; }
        .fallback { font-size: 12px; color: #64748b; word-break: break-all; background: #f1f5f9; padding: 10px; border-radius: 6px; }
        .note { font-size: 13px; color: #64748b; }
        .foot { padding: 16px 24px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">{{ config('app.name') }}</div>
        <div class="body">
            <p>Hello {{ $recipientName }},</p>
            <p>We received a request to reset the password for your account. Click the button below to choose a new one.</p>

            <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>

            <p class="note">
                This link expires in {{ $expiresInMinutes }} minutes and can only be used once.
                <strong>If you did not request a password reset, you can ignore this email</strong> —
                your password will not change.
            </p>

            <p class="note">If the button does not work, copy this address into your browser:</p>
            <p class="fallback">{{ $resetUrl }}</p>
        </div>
        <div class="foot">
            This is an automated message from {{ config('app.name') }}. Please do not reply.
        </div>
    </div>
</body>
</html>
