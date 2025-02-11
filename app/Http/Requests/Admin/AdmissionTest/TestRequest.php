<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\District;
use Illuminate\Foundation\Http\FormRequest;

class TestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'district_id' => 'required|integer|exists:'.District::class.',id',
            'address' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'testing_at' => 'required|date',
            'expect_end_at' => 'required|date|after:testing_at',
            'maximum_candidates' => 'required|integer|min:1',
            'is_public' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'district_id.required' => 'The district field is required.',
            'district_id.integer' => 'The district field must be an integer.',
            'district_id.exists' => 'The selected district is invalid.',
        ];
    }
}
