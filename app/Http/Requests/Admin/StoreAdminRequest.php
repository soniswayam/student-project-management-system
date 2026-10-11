<?php

namespace App\Http\Requests\Admin;

use App\Support\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('admins.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => [
                'required',
                Rule::exists('roles', 'name')->where('is_staff', true),
                Rule::when(! $this->user()?->isSuperAdmin(), [Rule::notIn(['super_admin'])]),
            ],
            'password' => ['required', 'confirmed', PasswordRules::password()],
        ];
    }
}
