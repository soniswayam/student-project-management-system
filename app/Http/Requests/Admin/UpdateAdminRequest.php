<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('admins.manage');
    }

    public function rules(): array
    {
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$admin->id],
            'role' => ['required', Rule::exists('roles', 'name')->where('is_staff', true)],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ];
    }
}
