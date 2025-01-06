<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['status' => 'required|boolean'];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required. if you are using our CMS, please contact I.T. officer.',
            'status.boolean' => 'The status field must be true or false. if you are using our CMS, please contact I.T. officer.',
        ];
    }
}
