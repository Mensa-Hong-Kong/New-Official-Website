<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestHasProctor;
use App\Models\User;
use Closure;
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
        $request = $this;

        return [
            'user_id' => [
                'required', 'integer', $unique,
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $request->merge(['user' => User::find($value)]);
                    if (! $request->user) {
                        $fail('The selected user id is invalid.');
                    }
                },
            ],
        ];
    }
}
