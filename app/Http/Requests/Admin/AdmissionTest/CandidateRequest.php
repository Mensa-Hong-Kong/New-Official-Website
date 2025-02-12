<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestHasCandidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique(AdmissionTestHasCandidate::class)
            ->where('test_id', $this->route('admission_test'));

        return ['user_id' => ['required', 'integer', $unique]];
    }
}
