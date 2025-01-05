<?php

namespace App\Http\Requests\Admin\Contact;

use App\Models\UserHasContact;
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
        $rules = ['required'];
        $contact = $this->route('contact');
        switch ($contact->type) {
            case 'email':
                $rules[] = 'email:rfc,dns';
                break;
            case 'mobile':
                $rules = ['required', 'integer', 'min_digits:5', 'max_digits:15'];
                break;
        }
        $rules[] = Rule::unique(UserHasContact::class, 'contact')
            ->where('user_id', $this->user()->id)
            ->where('type', $contact->type)
            ->ignore($contact->id);

        return [
            $contact->type => $rules,
            'is_verified' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'is_verified.boolean' => 'The verified field must be true or false. if you are using our CMS, please contact I.T. officer.',
            'is_default.boolean' => 'The default field must be true or false. if you are using our CMS, please contact I.T. officer.',
        ];
    }
}
