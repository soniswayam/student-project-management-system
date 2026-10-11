<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    public const KIND_DUE_SOON = 'due_soon';

    public const KIND_DUE_TODAY = 'due_today';

    public const KIND_OVERDUE_REVIEW = 'overdue_review';

    public const KIND_VIVA_TOMORROW = 'viva_tomorrow';

    protected $fillable = [
        'user_id',
        'remindable_type',
        'remindable_id',
        'kind',
        'sent_on',
    ];

    protected $casts = [
        'sent_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function alreadySent(int $userId, Model $thing, string $kind): bool
    {
        return static::where('user_id', $userId)
            ->where('remindable_type', $thing::class)
            ->where('remindable_id', $thing->getKey())
            ->where('kind', $kind)
            ->whereDate('sent_on', now()->toDateString())
            ->exists();
    }

    public static function record(int $userId, Model $thing, string $kind): bool
    {
        try {
            static::create([
                'user_id' => $userId,
                'remindable_type' => $thing::class,
                'remindable_id' => $thing->getKey(),
                'kind' => $kind,
                'sent_on' => now()->toDateString(),
            ]);

            return true;
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return false;
        }
    }
}
