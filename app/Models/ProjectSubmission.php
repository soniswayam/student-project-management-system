<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    protected $fillable = [
        'project_id',
        'report_path',
        'source_zip_path',
        'ppt_path',
        'screenshots',
        'submitted_at',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
