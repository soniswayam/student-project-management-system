<?php

namespace App\Http\Requests\Student;

use App\Models\Project;

class UpdateSynopsisRequest extends StoreSynopsisRequest
{
    protected function currentProject(): ?Project
    {
        return $this->user()?->student?->project();
    }
}
