<?php

namespace App\Http\Requests\User;

use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required', 'string', 'min:8', 'max:16',
                Rule::unique(User::class, 'username')
                    ->ignore($this->user()),
            ],
            'password' => [
                Rule::requiredIf($this->username != $this->user()->username || $this->new_password),
                'string', 'min:8', 'max:16',
            ],
            'new_password' => 'nullable|string|min:8|max:16|confirmed',
            'gender' => 'required|string|max:255',
            'birthday' => 'required|date|before_or_equal:'.now()->subYears(2)->format('Y-m-d'),
            'district_id' => 'nullable|integer|exists:'.District::class.',id',
            'address' => 'required_with:district_id|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'The password field is required when you change the username or password.',
            'district_id.integer' => 'The district field must be an integer.',
            'district_id.exists' => 'The selected district is invalid.',
            'address.required_with' => 'The address field is required when district is present.',
        ];
    }
}
