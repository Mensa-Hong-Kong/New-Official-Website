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
        $return = [
            'name' => 'required|string|max:255',
            'minimum_age' => 'nullable|integer|min:1|max:255',
            'maximum_age' => 'nullable|integer|min:1|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'quota' => 'required|integer|min:1|max:255',
        ];
        if ($this->minimum_age && $this->maximum_age) {
            $return['minimum_age'] .= '|lt:maximum_age';
            $return['maximum_age'] .= '|gt:minimum_age';
        }
        if ($this->start_at && $this->end_at) {
            $return['start_at'] .= '|before:end_at';
            $return['end_at'] .= '|after:start_at';
        }
        if ($this->method() == 'POST') {
            $return['price_name'] = 'nullable|string|max:255';
            $return['price'] = 'required|integer|min:1|max:65535';
        }

        return $return;
    }

    public function messages(): array
    {
        return [
            'minimum_age.lt' => 'The minimum age field must be less than maximum age field.',
            'maximum_age.gt' => 'The maximum age field must be greater than minimum age field.',
            'start_at.before' => 'The start at field must be a date before end at field.',
            'end_at.after' => 'The end at field must be a date after start at field.',
        ];
    }
}
