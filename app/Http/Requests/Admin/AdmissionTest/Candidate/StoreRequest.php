<?php

namespace App\Http\Requests\Admin\AdmissionTest\Candidate;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $request = $this;

        return [
            'user_id' => [
                'required', 'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $request->merge(['user' => User::find($value)]);
                    $test = $request->route('admission_test');
                    if (! $request->user) {
                        $fail('The selected user id is invalid.');
                    } elseif ($request->user->member?->is_active) {
                        $fail('The selected user id has already member.');
                    } elseif ($request->user->hasQualificationOfMembership) {
                        $fail('The selected user id has already qualification for membership.');
                    } elseif ($test->type->minimum_age && $test->type->minimum_age > floor($request->user->countAgeForPsychology($test->testing_at))) {
                        $fail('The selected user id age less than test minimum age limit.');
                    } elseif ($test->type->maximum_age && $test->type->maximum_age < floor($request->user->countAgeForPsychology($test->testing_at))) {
                        $fail('The selected user id age greater than test maximum age limit.');
                    } elseif ($request->user->admissionTests()->where('test_id', $test->id)->exists()) {
                        $fail('The selected user id has already schedule this admission test.');
                    } elseif ($request->function == 'schedule' && $request->user->futureAdmissionTest) {
                        $fail('The selected user id has already schedule other admission test.');
                    } elseif ($request->function == 'reschedule' && ! $request->user->futureAdmissionTest) {
                        $fail('The selected user id have no scheduled other admission test after than now.');
                    } elseif ($request->user->hasSamePassportAlreadyQualificationOfMembership) {
                        $fail('The selected user id has other same passport user account already been qualification for membership.');
                    } elseif ($request->user->hasOtherSamePassportUserAttendedAdmissionTest) {
                        $fail('The selected user id has other same passport user account attended admission test.');
                    } elseif (
                        $request->user->lastAttendedAdmissionTest &&
                        $request->user->lastAttendedAdmissionTest->testing_at
                            ->addMonths(
                                $request->user->lastAttendedAdmissionTest->type->interval_month
                            )->endOfDay() >= $request->route('admission_test')->testing_at
                    ) {
                        $fail("The selected user id has admission test record within {$request->user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$request->user->lastAttendedAdmissionTest->type->interval_month} months to now).");
                    } elseif (
                        ! $test->is_free &&
                        ! $request->is_free &&
                        ! $request->user->hasUnusedQuotaAdmissionTestOrder
                    ) {
                        $fail('The selected user id have no unused admission test quota, please select is free or let user to pay the admission fee.');
                    } elseif (
                        ! $test->is_free &&
                        ! $request->is_free &&
                        $request->user->hasUnusedQuotaAdmissionTestOrder->minimum_age &&
                        $request->user->hasUnusedQuotaAdmissionTestOrder->minimum_age > floor($request->user->countAge($request->user->hasUnusedQuotaAdmissionTestOrder->created_at))
                    ) {
                        $fail('The selected user id age less than the last order age limit.');
                    } elseif (
                        ! $test->is_free &&
                        ! $request->is_free &&
                        $request->user->hasUnusedQuotaAdmissionTestOrder->maximum_age &&
                        $request->user->hasUnusedQuotaAdmissionTestOrder->maximum_age < floor($request->user->countAge($request->user->hasUnusedQuotaAdmissionTestOrder->created_at))
                    ) {
                        $fail('The selected user id age greater than the last order age limit.');
                    } elseif (! $request->user->defaultEmail && ! $request->user->defaultMobile) {
                        $fail('The selected user must at least has one default contact.');
                    }
                },
            ],
            'is_free' => 'nullable|boolean',
            'function' => 'required|string|in:schedule,reschedule',
        ];
    }

    public function messages(): array
    {
        return ['function.in' => 'The function field does not exist in schedule, reschedule.'];
    }
}
