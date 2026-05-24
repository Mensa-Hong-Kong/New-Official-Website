<?php

namespace App\Http\Requests\Admin\AdmissionTest\Order;

use App\Models\AdmissionTest;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $request = $this;

        return [
            'bypass_expiration_date_checking' => 'nullable|boolean',
            'function' => [
                'required', 'string', 'in:schedule,reschedule',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $order = $request->route('order');
                    switch ($value) {
                        case 'schedule':
                            if (
                                $order->user->lastAdmissionTest &&
                                $order->user->lastAdmissionTest->pivot_is_present === null
                            ) {
                                $fail('The order of user has already been scheduled other admission test.');
                            }
                            break;
                        case 'reschedule':
                            if (
                                ! $order->user->lastAdmissionTest ||
                                $order->user->lastAdmissionTest->pivot_is_present !== null
                            ) {
                                $fail('The order of user have no scheduled other admission test.');
                            } elseif (
                                $order->user->lastAdmissionTest?->pivot_is_present === null &&
                                $order->user->lastAdmissionTest->testing_at < now()->addHours(2)
                            ) {
                                $fail('The order of user id scheduled other admission test and after than before testing time 2 hours, please wait proctor to confirm the user is absent first.');
                            }
                            break;
                    }
                },
            ],
            'test_id' => [
                'required', 'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $request->merge([
                        'test' => AdmissionTest::withCount('candidates')
                            ->find($value),
                    ]);
                    $order = $request->route('order');
                    if (! $request->test) {
                        $fail('The selected test is invalid, may be the test is not exist or the test has been delete, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.');
                    } elseif ($request->test->is_free) {
                        $fail('The admission test order cannot select free admission test.');
                    } elseif ($request->test->candidates_count >= $request->test->maximum_candidates) {
                        // checking of lesser use row id because auto increment counter is not reset to its value before the transaction began
                        $fail('The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.');
                    } elseif (
                        $request->test->type->minimum_age &&
                        $request->test->type->minimum_age > floor($order->user->countAgeForPsychology($request->test->testing_at))
                    ) {
                        $fail('The order of user age less than selected test minimum age limit.');
                    } elseif (
                        $request->test->type->maximum_age &&
                        $request->test->type->maximum_age < floor($order->user->countAgeForPsychology($request->test->testing_at))
                    ) {
                        $fail('The order of user age greater than selected test maximum age limit.');
                    } elseif ($request->test->candidates()->where('user_id', $order->user_id)->exists()) {
                        $fail('The order of user has already been scheduled for the selected admission test.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return ['function.in' => 'The function field does not exist in schedule, reschedule.'];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $order = $this->route('order');
                if (! $order->hasUnusedQuota) {
                    $validator->errors()->add('failed', 'The order have no unused quota.');
                } elseif (
                    ! $this->bypass_expiration_date_checking &&
                    $order->quotaExpiredOn && $this->test &&
                    $order->quotaExpiredOn < $this->test->testing_at
                ) {
                    $validator->errors()->add(
                        'failed',
                        'The order admission test quota expired before the testing time of selected admission test, please select other admission test or bypass expiration date checking.'
                    );
                } elseif (
                    $order->user->lastAttendedAdmissionTest &&
                    (clone $order->user->lastAttendedAdmissionTest->testing_at)
                        ->addMonths(
                            $order->user->lastAttendedAdmissionTest->type->interval_month
                        )->endOfDay() >= $this->test->testing_at
                ) {
                    $validator->errors()->add(
                        'failed',
                        "The order of user has admission test record within {$order->user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$order->user->lastAttendedAdmissionTest->type->interval_month} months to now)."
                    );
                } elseif (! $order->user->defaultEmail && ! $order->user->defaultMobile) {
                    $validator->errors()->add('failed', 'The order of user must at least has one default contact.');
                } elseif ($order->user->member?->is_active) {
                    $validator->errors()->add('failed', 'The order of user has already been member.');
                } elseif ($order->user->hasQualificationOfMembership) {
                    $validator->errors()->add('failed', 'The order of user has already been qualification for membership.');
                } elseif ($order->user->hasSamePassportAlreadyQualificationOfMembership) {
                    $validator->errors()->add('failed', 'The order of user has other same passport user account already been qualification for membership.');
                } elseif ($order->user->hasOtherSamePassportUserAttendedAdmissionTest) {
                    $validator->errors()->add('failed', 'The order of user has other same passport user account attended admission test.');
                }
            },
        ];
    }
}
