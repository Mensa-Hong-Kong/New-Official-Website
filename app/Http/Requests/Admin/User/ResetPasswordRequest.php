<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_type' => 'required|string|in:email,mobile',
        ];
    }

    public function messages(): array
    {
        $return = [
            'contact_type.required' => 'The contact type field is required, if you are using our CMS, please contact I.T. officer.',
            'contact_type.string' => 'The contact type field must be a string, if you are using our CMS, please contact I.T. officer.',
            'contact_type.in' => 'The selected contact type is invalid, if you are using our CMS, please contact I.T. officer.',
        ];

        return $return;
    }
}
