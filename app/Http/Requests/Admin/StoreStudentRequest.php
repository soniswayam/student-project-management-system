<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('students.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no'],
            'department_id' => ['required', 'exists:departments,id'],
            'semester' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(User::STATUSES)],
            'password' => ['required', 'confirmed', Password::min(6)],
        ];
    }
}
