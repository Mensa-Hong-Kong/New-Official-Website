<?php

namespace App\Http\Requests\Admin\AdmissionTest\Candidate;

use App\Models\AdmissionTestHasCandidate;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique(AdmissionTestHasCandidate::class)
            ->where('test_id', $this->route('admission_test'));

        return [
            'user_id' => ['required', 'integer', $unique],
            'function' => 'required|string|in:schedule,reschedule',
        ];
    }

    public function messages(): array
    {
        return ['function.in' => 'The function field does not exist in schedule, reschedule.'];
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
                    if ($this->user->isActiveMember()) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id has already member.'
                        );
                    } elseif ($this->user->hasQualificationOfMembership()) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id has already qualification for membership.'
                        );
                    } elseif ($this->user->futureAdmissionTest && $user->futureAdmissionTest->id == $admissionTest->id) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id has already schedule this admission test.'
                        );
                    } elseif ($this->function == 'schedule' && $user->futureAdmissionTest) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id has already schedule other admission test.'
                        );
                    } elseif ($this->function == 'reschedule' && ! $user->futureAdmissionTest) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id have no scheduled other admission test after than now.'
                        );
                    } elseif ($user->hasSamePassportAlreadyQualificationOfMembership()) {
                        $validator->errors()->add(
                            'user_id',
                            'The passport of selected user id has already been qualification for membership.'
                        );
                    } elseif ($user->hasOtherSamePassportUserTested()) {
                        $validator->errors()->add(
                            'user_id',
                            'The selected user id has other same passport user account tested.'
                        );
                    } elseif (
                        $user->lastAdmissionTest &&
                        $user->hasTestedWithinDateRange(
                            $admissionTest->testing_at->subMonths(
                                $user->lastAdmissionTest->type->interval_month
                            ), $now
                        )
                    ) {
                        $validator->errors()->add(
                            'user_id',
                            "The selected user id has admission test record within {$user->lastAdmissionTest->type->interval_month} months(count from testing at of this test sub {$user->lastAdmissionTest->type->interval_month} months to now)."
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
