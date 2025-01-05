<?php

namespace App\Http\Requests\Admin\User;

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

        return [
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
        ];
    }

    public function messages(): array
    {
        return [
            'passport_type_id.required' => 'The passport type field is required.',
            'passport_type_id.exists' => 'The selected passport type is invalid.',
        ];
    }
}
