<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'minimum_age' => 'nullable|integer|min:1|max:255|lt:maximum_age',
            'maximum_age' => 'nullable|integer|min:1|max:255|gt:minimum_age',
        ];
    }

    public function messages(): array
    {
        return [
            'minimum_age.lt' => 'The minimum age field must be less than maximum age.',
            'maximum_age.gt' => 'The maximum age field must be greater than minimum age.',
        ];
    }
}
