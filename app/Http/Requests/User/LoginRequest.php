<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
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

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = User::with([
                'loginLogs' => function ($query) {
                    $query->where('status', false)
                        ->where('created_at', '>=', now()->subDay());
                },
            ])->firstWhere('username', $this->username);
            if (! $user || ! $user->checkPassword($this->password)) {
                if ($user && ! $user->checkPassword($this->password)) {
                    $user->loginLogs()->create();
                }
                $validator->errors()->add('failed', 'The provided username or password is incorrect.');
            } elseif ($user->loginLogs->count() >= 10) {
                $firstInRangeLoginFailedTime = $user->loginLogs[0]['created_at'];

                abort(429, "Too many failed login attempts. Please try again later than $firstInRangeLoginFailedTime.");
            } else {
                $this->merge(['user' => $user]);
            }
        });
    }
}
