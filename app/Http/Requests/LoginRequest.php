<?php

namespace App\Http\Requests;

use App\Models\PassportType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:8|max:16',
            'password' => 'required|string|min:8|max:16',
            'remember_me' => 'sometimes|boolean',
        ];
    }
}
