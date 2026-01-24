<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Library\Stripe\Rules\Amount\Stripe;
use Illuminate\Foundation\Http\FormRequest;

class PriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [
            'name' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
        ];
        if ($this->method() == 'POST') {
            $return['price'] = ['required', new Stripe];
        }

        return $return;
    }
}
