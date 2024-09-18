<?php

namespace App\Http\Requests;

use App\Models\PassportType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'username' => 'required|string|min:8|max:16',
            'password' => 'required|string|min:8|max:16|confirmed',
            'family_name' => 'required|string|max:255',
            'midden_name' => 'nullable|string|max:255',
            'given_name' => 'required|string|max:255',
            'passport_type_id' => 'required|integer|exists:'.PassportType::class.',id',
            'passport_number' => [
                'required', 'string', 'min:8', 'max:18', 'regex:/^[A-Z0-9]+$/',
                Rule::unique(User::class, 'passport_number')
                    ->where('passport_type_id', $this->passport_type_id)
            ],
            'gender' => 'required|string|max:255',
            'birthday' => 'required|date|before_or_equal:'.now()->subYears(2)->format('Y-m-d'),
            'email' => 'nullable|email:rfc,dns',
            'mobile' => 'nullable|integer|min_digits:5|max_digits:15',
        ];
    }
}