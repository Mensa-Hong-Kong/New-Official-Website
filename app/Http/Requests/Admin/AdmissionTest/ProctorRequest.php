<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestHasProctor;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
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

    public function after()
    {
        return [
            function (Validator $validator) {
                $user = User::find($this->user_id);
                if (! $user) {
                    $validator->errors()->add(
                        'user_id',
                        'The selected user id is invalid.'
                    );
                } else {
                    $this->merge(['user' => $user]);
                }
            },
        ];
    }
}
