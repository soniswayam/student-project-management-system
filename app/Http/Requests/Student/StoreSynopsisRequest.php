<?php

namespace App\Http\Requests\Student;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSynopsisRequest extends FormRequest
{
    /** Only a logged-in student may submit a synopsis. */
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
            // Partner is required only for group projects.
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

    /** Cross-field business rules: leader ≠ partner, and partner must be free. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('project_type') !== 'group') {
                return;
            }

            $partnerId = (int) $this->input('partner_student_id');
            if (! $partnerId) {
                return;
            }

            $student = $this->user()->student;

            if ($student && $partnerId === $student->id) {
                $validator->errors()->add('partner_student_id', 'The partner cannot be the same as the leader.');

                return;
            }

            $partner = Student::find($partnerId);
            if ($partner && $partner->hasProject()) {
                $validator->errors()->add('partner_student_id', 'The selected partner already belongs to another project.');
            }
        });
    }
}
