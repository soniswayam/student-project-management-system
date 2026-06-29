<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReview extends Model
{
    protected $fillable = [
        'project_id',
        'faculty_id',
        'stage',
        'action',
        'comments',
        'marks',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            'approved', 'reviewed' => 'success',
            'rejected' => 'danger',
            'correction' => 'warning',
            default => 'secondary',
        };
    }
}
