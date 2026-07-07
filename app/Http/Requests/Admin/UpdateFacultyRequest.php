<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateFacultyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('faculty.manage');
    }

    public function messages(): array
    {
        return [
            'department_ids.required' => 'Select at least one department/course for this faculty.',
            'department_ids.min' => 'Select at least one department/course for this faculty.',
        ];
    }

    public function rules(): array
    {
        $faculty = $this->route('faculty');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$faculty->user_id],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ];
    }
}
