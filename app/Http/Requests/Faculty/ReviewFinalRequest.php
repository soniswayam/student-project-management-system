<?php

namespace App\Http\Requests\Faculty;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class ReviewFinalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'faculty';
    }

    public function rules(): array
    {
        $max = $this->project()?->maxMarks();

        return [
            'comments' => ['nullable', 'string', 'max:2000'],
            'marks' => $max === null
                ? ['nullable', 'prohibited']
                : ['nullable', 'integer', 'min:0', 'max:'.$max],
            'final_remarks' => ['nullable', 'string', 'max:2000'],
            'complete' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        $level = $this->project()?->department?->levelLabel() ?? 'This level';

        return [
            'marks.prohibited' => "{$level} projects are not marked, so no marks can be recorded.",
            'marks.max' => "Marks cannot exceed this level's maximum of ".($this->project()?->maxMarks() ?? 0).'.',
        ];
    }

    private function project(): ?Project
    {
        $project = $this->route('project');

        return $project instanceof Project ? $project : null;
    }
}
