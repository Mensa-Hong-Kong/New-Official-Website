<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class VerifyContactRequest extends FormRequest
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
