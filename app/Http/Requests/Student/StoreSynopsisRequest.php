<?php

namespace App\Http\Requests\Student;

use App\Models\DegreeLevelSetting;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSynopsisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student';
    }

    public function rules(): array
    {
        return [
            'project_type' => ['required', 'in:single,group'],
            'name' => ['required', 'string', 'max:255'],
            'frontend_tech' => ['required', 'string', 'max:255'],
            'backend_tech' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'min:30'],
            'partner_student_id' => [
                Rule::requiredIf(fn () => $this->input('project_type') === 'group'),
                'nullable',
                'exists:students,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'partner_student_id.required' => 'Please select a partner for a group project.',
        ];
    }

    protected function currentProject(): ?Project
    {
        return null;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('project_type') !== 'group') {
                return;
            }

            $student = $this->user()->student;

            $maxTeam = DegreeLevelSetting::groupSizeFor($student?->department?->level);

            if ($maxTeam < 2) {
                $validator->errors()->add(
                    'project_type',
                    ($student?->department?->levelLabel() ?? 'Your course').' projects must be done individually.'
                );

                return;
            }

            $partnerId = (int) $this->input('partner_student_id');
            if (! $partnerId) {
                return;
            }

            if ($student && $partnerId === $student->id) {
                $validator->errors()->add('partner_student_id', 'You cannot add yourself as your own group member.');

                return;
            }

            $partner = Student::with('user')->find($partnerId);
            $current = $this->currentProject();

            if ($partner && ($partner->user?->role !== 'student' || $partner->user?->status !== User::STATUS_ACTIVE)) {
                $validator->errors()->add('partner_student_id', 'The selected partner cannot be added to a project.');

                return;
            }

            $partnerIsTaken = $partner
                && $partner->membership
                && (! $current || $partner->membership->project_id !== $current->id);

            if ($partnerIsTaken) {
                $validator->errors()->add('partner_student_id', 'The selected partner already belongs to another project.');

                return;
            }

            if ($partner && $student && $partner->department_id !== $student->department_id) {
                $validator->errors()->add('partner_student_id', 'Your partner must be from your own department.');

                return;
            }

            if ($partner && $student && $partner->semester !== $student->semester) {
                $validator->errors()->add('partner_student_id', 'Your partner must be in your own semester.');
            }
        });
    }
}
