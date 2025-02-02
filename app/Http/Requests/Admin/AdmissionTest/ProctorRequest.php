<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestHasProctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique(AdmissionTestHasProctor::class)
            ->where('test_id', $this->route('admission_test'));
        if ($this->method() != 'POST') {
            $unique = $unique->ignore($this->route('proctor')->id, 'user_id');
        }

        return ['user_id' => ['required', 'integer', $unique]];
    }
}
