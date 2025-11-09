<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [
            'name' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
        ];
        if ($this->method() == 'POST') {
            $return['price'] = ['required', Rule::numeric()->min(0.01)->max(99999.99)->decimal(0, 2)];
        }

        return $return;
    }
}
