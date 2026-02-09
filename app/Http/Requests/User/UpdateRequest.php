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
        $districtUtility = 'nullable';
        $addressUtility = 'required_with:district_id';
        if(
            $this->user()->member?->isActive ||
            $this->user()->member?->orders()->where('expired_at', '>', now())->exists()
        ) {
            $districtUtility = 'required';
            $addressUtility = 'required';
        }

        return [
            'username' => [
                'required', 'string', 'min:8', 'max:16',
                Rule::unique(User::class, 'username')
                    ->ignore($this->user()),
            ],
            'password' => [
                'bail', Rule::requiredIf($this->username != $this->user()->username || $this->new_password),
                'string', 'min:8', 'max:16', 'current_password:web',
            ],
            'new_password' => 'nullable|string|min:8|max:16|confirmed',
            'gender' => 'required|string|max:255',
            'birthday' => 'required|date|before_or_equal:'.now()->subYears(2)->format('Y-m-d'),
            'district_id' => $districtUtility.'|integer|exists:'.District::class.',id',
            'address' => $addressUtility.'|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'The password field is required when you change the username or password.',
            'password.current_password' => 'The provided password is incorrect.',
            'district_id.required' => 'The district field is required when you are an active member or have membership order in progress.',
            'district_id.integer' => 'The district field must be an integer.',
            'district_id.exists' => 'The selected district is invalid.',
            'address.required' => 'The address field is required when you are an active member or have membership order in progress.',
            'address.required_with' => 'The address field is required when district is present.',
        ];
    }
}
