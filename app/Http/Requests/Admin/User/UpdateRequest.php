<?php

namespace App\Http\Requests\Admin\User;

use App\Models\District;
use App\Models\PassportType;
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
        $user = $this->route('user');
        $districtUtility = 'nullable';
        $addressUtility = 'required_with:district_id';
        $return = [];
        if ($this->route('user')->member) {
            $return = array_merge($return, [
                'prefix_name' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'suffix_name' => 'nullable|string|max:255',
            ]);
            if (
                $this->user()->member->isActive ||
                $this->user()->member->orders()->where('expired_at', '>', now())->exists()
            ) {
                $districtUtility = 'required';
                $addressUtility = 'required';
            }
        }

        return array_merge($return, [
            'username' => [
                'required', 'string', 'min:8', 'max:16',
                Rule::unique(User::class, 'username')
                    ->ignore($user),
            ],
            'family_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'given_name' => 'required|string|max:255',
            'passport_type_id' => 'required|integer|exists:'.PassportType::class.',id',
            'passport_number' => 'required|regex:/^[A-Z0-9]+$/|min:8|max:18',
            'gender' => 'required|string|max:255',
            'birthday' => 'required|date|before_or_equal:'.now()->subYears(2)->format('Y-m-d'),
            'district_id' => $districtUtility.'|integer|exists:'.District::class.',id',
            'address' => $addressUtility.'|string|max:255',
        ]);
    }

    public function messages(): array
    {
        return [
            'passport_type_id.required' => 'The passport type field is required.',
            'passport_type_id.exists' => 'The selected passport type is invalid.',
            'passport_number.regex' => 'The passport number field format is invalid. It should only contain uppercase letters and numbers.',
            'district_id.required' => 'The district field is required when user is an active member or has membership order in progress.',
            'district_id.integer' => 'The district field must be an integer.',
            'district_id.exists' => 'The selected district is invalid.',
            'address.required_with' => 'The address field is required when district is present.',
            'address.required' => 'The address field is required when user is an active member or has membership order in progress.',
        ];
    }
}
