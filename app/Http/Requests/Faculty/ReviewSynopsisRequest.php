<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class ReviewSynopsisRequest extends FormRequest
{
    /** Faculty ownership of the project is enforced in the controller. */
    public function authorize(): bool
    {
        return $this->user()?->role === 'faculty';
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approved,rejected,correction'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
