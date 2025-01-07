<?php

namespace App\Http\Requests\Admin\Contact;

use App\Models\User;
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
            'user_id' => 'required|integer|exists:'.User::class.',id',
            'email' => [
                'required_without_all:mobile', 'missing_with:mobile', 'email:rfc,dns',
                Rule::unique(UserHasContact::class, 'contact')
                    ->where('user_id', $this->user_id)
                    ->where('type', 'email'),
            ],
            'mobile' => [
                'required_without_all:email', 'missing_with:email',
                'integer', 'min_digits:5', 'max_digits:15',
                Rule::unique(UserHasContact::class, 'contact')
                    ->where('user_id', $this->user_id)
                    ->where('type', 'mobile'),
            ],
            'is_verified' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user field is required, if you are using our CMS, please contact I.T. officer.',
            'user_id.integer' => 'The user field must be an integer, if you are using our CMS, please contact I.T. officer.',
            'user_id.exists' => 'User is ont found, may be deleted, if you are using our CMS, please refresh. If refresh is not show 404, please contact I.T. officer.',
            'email.required_without_all' => 'The data fields of :attribute, :values must have one.',
            'email.missing_with' => 'The data fields of :attribute, :values only can have one.',
            'is_verified.boolean' => 'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.',
            'is_default.boolean' => 'The default field must be true or false. if you are using our CMS, please contact I.T. officer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if (
            (
                ! str_ends_with($message, ' have one.') ||
                ! str_starts_with($message, 'The data fields of ')
            ) && ! in_array($message, [
                'The user field is required, if you are using our CMS, please contact I.T. officer.',
                'The user field must be an integer, if you are using our CMS, please contact I.T. officer.',
                'User is ont found, may be deleted, if you are using our CMS, please refresh. If refresh is not show 404, please contact I.T. officer.',
                'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.',
                'The default field must be true or false. if you are using our CMS, please contact I.T. officer.',

            ])
        ) {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
