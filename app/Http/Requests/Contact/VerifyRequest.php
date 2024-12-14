<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->contact->user_id == $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|size:6|alpha_num',
        ];
    }
}
