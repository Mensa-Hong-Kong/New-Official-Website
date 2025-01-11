<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['name' => 'nullable|string|not_regex:/:/'];
    }

    public function messages(): array
    {
        return [
            'name.not_regex' => 'The name field cannot has ";".',
        ];
    }
}
