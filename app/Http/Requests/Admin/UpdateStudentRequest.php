<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('students.edit');
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$student->user_id],
            'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no,'.$student->id],
            'department_id' => ['required', 'exists:departments,id'],
            'semester' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_PENDING, User::STATUS_BLOCKED])],
            'password' => ['nullable', 'confirmed', PasswordRules::password()],
        ];
    }
}
