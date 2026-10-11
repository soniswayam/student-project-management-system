<?php

namespace App\Models;

use App\Mail\NotificationMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Illuminate\Support\defer;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'link',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function notify(int $userId, string $title, string $message, ?string $link = null): void
    {
        static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        defer(fn () => static::sendEmail($userId, $title, $message, $link));
    }

    protected static function sendEmail(int $userId, string $title, string $message, ?string $link): void
    {
        $user = User::find($userId);

        if (! $user || ! $user->email) {
            return;
        }

        try {
            Mail::to($user->email)->send(
                new NotificationMail($title, $message, $link, $user->name)
            );
        } catch (\Throwable $e) {
            Log::warning('Notification email failed: '.$e->getMessage());
        }
    }
}
