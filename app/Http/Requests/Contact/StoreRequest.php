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
        $return = [
            'type' => 'required|string|in:email,mobile',
            'contact' => ['required'],
        ];
        switch($this->type) {
            case 'email':
                $return['contact'][] = 'email:rfc,dns';
                break;
            case 'mobile':
                $return['contact'] = ['required', 'integer', 'min_digits:5', 'max_digits:15'];
                break;
        }
        $return['contact'][] = Rule::unique(UserHasContact::class, 'contact')
            ->where('user_id', $this->user()->id)
            ->where('type', $this->type);
        return $return;
    }

    public function messages(): array
    {
        $return = [
            'type.required' => 'The type field is required, if you are using our CMS, please contact I.T. officer.',
            'type.string' => 'The type field must be a string, if you are using our CMS, please contact I.T. officer.',
            'type.in' => 'The selected type is invalid, if you are using our CMS, please contact I.T. officer.',
        ];
        if(!is_array($this->type)) {
            $return = array_merge($return, [
                'contact.required' => "The contact of {$this->type} is required.",
                'contact.email' => 'The contact of email must be a valid email address.',
                'contact.integer' => "The contact of {$this->type} must be an integer.",
                'contact.min_digits' => "The contact of {$this->type} must have at least :min digits.",
                'contact.max_digits' => "The contact of {$this->type} must not have more than :max digits.",
                'contact.unique' => "The contact of {$this->type} has already been taken.",
            ]);
        }
        return $return;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if (! str_ends_with($message, 'please contact I.T. officer.')) {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
