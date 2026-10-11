<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollegeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('settings.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'project_grace_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'assignment_grace_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'duplicate_warn_percent' => ['required', 'integer', 'min:1', 'max:100', 'lte:duplicate_block_percent'],
            'duplicate_block_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'reminder_lead_days' => ['required', 'integer', 'min:0', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'duplicate_warn_percent.lte' => 'The warning threshold must be lower than (or equal to) the blocking threshold.',
        ];
    }
}
