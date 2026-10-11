<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('roles.manage');
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(Role::allKeys())],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $slug = str((string) $this->input('label'))->slug('_')->toString();

                if ($slug === '') {
                    $validator->errors()->add('label', 'Please use letters or numbers in the role name.');
                } elseif (Role::where('name', $slug)->exists()) {
                    $validator->errors()->add('label', 'A role with this name already exists.');
                }
            },
        ];
    }
}
