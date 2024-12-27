<?php

namespace App\Http\Requests\Contact;

use App\Models\UserHasContact;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required_without_all:mobile', 'missing_with:mobile', 'email:rfc,dns',
                Rule::unique(UserHasContact::class, 'contact')
                    ->where('user_id', $this->user()->id)
                    ->where('type', 'email'),
            ],
            'mobile' => [
                'required_without_all:email', 'missing_with:email',
                'integer', 'min_digits:5', 'max_digits:15',
                Rule::unique(UserHasContact::class, 'contact')
                    ->where('user_id', $this->user()->id)
                    ->where('type', 'mobile'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without_all' => 'The data fields of :attribute, :values must have one.',
            'email.missing_with' => 'The data fields of :attribute, :values only can have one.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if (
            ! str_ends_with($message, ' have one.') ||
            ! str_starts_with($message, 'The data fields of ')
        ) {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
