<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:7|max:320', // minimum email: ab@c.de
            'password' => 'required|string|min:8|max:16',
            'remember_me' => 'sometimes|boolean',
        ];
    }
}
