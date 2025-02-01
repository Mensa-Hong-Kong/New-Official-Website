<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 'integer',
                Rule::unique('admission_test_has_proctors')
                    ->where('test_id', $this->route('admission_test')),
            ],
        ];
    }
}
