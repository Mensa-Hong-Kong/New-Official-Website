<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestHasCandidate;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
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
                    $now = now();
                    $admissionTest = $this->route('admission_test');
                    $this->merge([
                        'user' => $user,
                        'now' => $now,
                    ]);
                    if ($user->hasSamePassportAlreadyQualificationOfMembership()) {
                        $validator->errors()->add(
                            'user_id',
                            'The passport of selected user id has already been qualification for membership.'
                        );
                    } elseif ($user->hasSamePassportTestedWithinDateRange($admissionTest->testing_at->subMonths(6), $now)) {
                        $validator->errors()->add(
                            'user_id',
                            'The passport of selected user id has admission test record within 6 months(count from testing at of this test sub 6 months to now).'
                        );
                    } elseif ($user->hasSamePassportTestedTwoTimes()) {
                        $validator->errors()->add(
                            'user_id',
                            'The passport of selected user id tested two times admission test.'
                        );
                    } elseif (! $user->defaultEmail && ! $user->defaultMobile) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user must at least has default contact.'
                        );
                    }
                }
            },
        ];
    }
}
