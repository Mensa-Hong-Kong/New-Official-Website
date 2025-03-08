<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['function' => 'required|string|in:schedule,reschedule'];
    }

    public function messages(): array
    {
        return ['function.in' => 'The function field does not exist in schedule, reschedule.'];
    }
}
