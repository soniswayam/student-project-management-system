<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class ReviewFinalRequest extends FormRequest
{
    /** Faculty ownership of the project is enforced in the controller. */
    public function authorize(): bool
    {
        return $this->user()?->role === 'faculty';
    }

    public function rules(): array
    {
        return [
            'comments' => ['nullable', 'string', 'max:2000'],
            'marks' => ['nullable', 'integer', 'min:0', 'max:100'],
            'final_remarks' => ['nullable', 'string', 'max:2000'],
            'complete' => ['nullable', 'boolean'],
        ];
    }
}
