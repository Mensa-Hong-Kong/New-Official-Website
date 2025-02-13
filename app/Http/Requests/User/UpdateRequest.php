<?php

namespace App\Http\Requests\User;

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
        ];
    }

    public function messages(): array
    {
        return ['password.required' => 'The password field is required when you change the username or password.'];
    }
}
