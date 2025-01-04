<?php

namespace App\Http\Requests\Admin\User;

use App\Models\PassportType;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $return =  [
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
        if(count($user->emails)) {
            $return['emails'] = 'required|array';
        }
        if(count($user->mobiles)) {
            $return['mobiles'] = 'required|array';
        }
        foreach($user->contacts as $contact) {
            switch($contact->type) {
                case 'email':
                    $return["{$contact->type}s.{$contact->id}.contact"] = 'required|email:rfc,dns';
                    break;
                case 'mobile':
                    $return["{$contact->type}s.{$contact->id}.contact"] = 'required|integer|min_digits:5|max_digits:15';
                    break;
            }
            $return["{$contact->type}s.{$contact->id}.is_default"] = 'sometimes|boolean';
            $return["{$contact->type}s.{$contact->id}.is_verified"] = 'sometimes|boolean';
        }
        return $return;
    }

    public function messages(): array
    {
        return [
            'passport_type_id.required' => 'The passport type field is required.',
            'passport_type_id.exists' => 'The selected passport type is invalid.',
            'emails.required' => 'The emails field is required.',
            'emails.array' => 'The emails field must be an array.',
            'emails.*.contact.required' => 'The email field is required.',
            'emails.*.contact.email' => 'The email field must be a valid email address.',
            'emails.*.is_default.boolean' => 'The email defaule field must be true or false.',
            'emails.*.is_verified.boolean' => 'The email verified field must be true or false.',
            'mobiles.required' => 'The mobiles field is required.',
            'mobiles.array' => 'The mobiles field must be an array.',
            'mobiles.*.contact.required' => 'The mobile field is required.',
            'mobiles.*.contact.integer' => 'The mobile field must be an integer.',
            'mobiles.*.contact.min_digits' => 'The mobile field must have at least 5 digits.',
            'mobiles.*.contact.max_digits' => 'The mobile field must not have more than 15 digits.',
            'mobiles.*.is_default.boolean' => 'The mobile defaule field must be true or false.',
            'mobiles.*.is_verified.boolean' => 'The mobile verified field must be true or false.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $keys = $errors->keys();
        $return = ['message' => []];
        foreach($keys as $key) {
            $message = $errors->get($key);
            if(
                in_array($key, ['emails', 'mobiles']) ||
                preg_match('/^(emails|mobiles).([0-9]+).(is_default|is_verified)$/', $key)
            ) {
                $return['message'] = array_merge($return['message'], $message);
                continue;
            }
            $return[$key] = $message;
        }
        if(count($return['message'])) {
            $return['message'][] = 'If you are using our CMS, plesae contact I.T. officer.';
            $return['message'] = implode(" ", $return['message']);
        } else {
            unset($return['message']);
        }

        throw ValidationException::withMessages($return);
    }
}
