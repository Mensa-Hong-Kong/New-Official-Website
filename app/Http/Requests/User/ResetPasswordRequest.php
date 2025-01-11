<?php

namespace App\Http\Requests\User;

use App\Models\PassportType;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [
            'passport_type_id' => 'required|integer|exists:'.PassportType::class.',id',
            'passport_number' => 'required|regex:/^[A-Z0-9]+$/|min:8|max:18',
            'birthday' => 'required|date|before_or_equal:'.now()->subYears(2)->format('Y-m-d'),
            'verified_contact_type' => 'required|string|in:email,mobile',
            'verified_contact' => 'required',
        ];
        switch ($this->verified_contact_type) {
            case 'email':
                $return['verified_contact'] .= '|email:rfc,dns';
                break;
            case 'mobile':
                $return['verified_contact'] .= '|integer|min_digits:5|max_digits:15';
                break;
        }

        return $return;
    }

    public function messages(): array
    {
        $return = [
            'passport_type_id.required' => 'The passport type field is required.',
            'passport_type_id.exists' => 'The selected passport type is invalid.',
        ];
        switch ($this->verified_contact_type) {
            case 'email':
                $return['verified_contact.email'] = 'The verified contact of email must be a valid email address.';
                break;
            case 'mobile':
                $return['verified_contact.integer'] = 'The verified contact of mobile must be an integer.';
                $return['verified_contact.min_digits'] = 'The verified contact of mobile must have at least 5 digits.';
                $return['verified_contact.max_digits'] = 'The verified contact of mobile must not have more than 15 digits.';
                break;
        }

        return $return;
    }
}
