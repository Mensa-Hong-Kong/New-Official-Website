<?php

namespace App\Http\Requests\Admin\AdmissionTest\Order;

use App\Models\AdmissionTest;
use App\Models\OtherPaymentGateway;
use App\Models\User;
use Closure;
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
        $request = $this;
        $exists = Rule::exists(OtherPaymentGateway::class, 'id')
            ->where('is_active', true);

        return [
            'user_id' => [
                'required', 'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $request->merge(['user' => User::find($value)]);
                    if (! $request->user) {
                        $fail('The selected user id is invalid.');
                    } elseif (! $request->user->defaultEmail && ! $request->user->defaultMobile) {
                        $fail('The selected user must at least has one default contact.');
                    } elseif ($request->user->isActiveMember) {
                        $fail('The selected user id has already member.');
                    } elseif ($request->user->hasQualificationOfMembership) {
                        $fail('The selected user id has already qualification for membership.');
                    } elseif ($request->test_id && $request->user->futureAdmissionTest) {
                        $fail('The selected user id has been scheduled admission test.');
                    } elseif ($request->user->hasUnusedQuotaAdmissionTestOrder) {
                        $fail('The selected user has unused quota.');
                    } elseif ($request->user->hasSamePassportAlreadyQualificationOfMembership) {
                        $fail('The passport of selected user id has already been qualification for membership.');
                    } elseif ($request->user->lastAttendedAdmissionTestOfOtherSamePassportUser) {
                        $fail('The selected user id has other same passport user account tested.');
                    }
                },
            ],
            'product_name' => 'nullable|string|max:255',
            'price_name' => 'nullable|string|max:255',
            'price' => 'required|integer|min:1|max:65535',
            'quota' => 'required|integer|min:1|max:255',
            'status' => 'required|string|in:pending,succeeded',
            'expired_at' => [
                'required_if:status,pending', 'date',
                'after_or_equal:'.now()->addMinutes(5)->format('Y-m-d H:i'),
                'before_or_equal:'.now()->addDay()->format('Y-m-d H:i'),
            ],
            'payment_gateway_id' => ['required', 'integer', $exists],
            'reference_number' => 'nullable|string|max:255',
            'test_id' => [
                'nullable', 'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    $request->merge([
                        'test' => AdmissionTest::withCount('candidates')
                            ->find($value),
                    ]);
                    if (! $request->test) {
                        $fail('The selected test is invalid, may be the test is not exist or the test has been delete, The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.');
                    } elseif ($request->test->candidates_count >= $request->test->maximum_candidates) {
                        // checking of lesser use row id because auto increment counter is not reset to its value before the transaction began
                        $fail('The admission test is fulled, please select other test, if you need update to date tests info, please reload the page or open a new window tab to read tests info.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'expired_at.after_or_equal' => 'The expired at field must be a date after or equal to 5 minutes.',
            'expired_at.before_or_equal' => 'The expired at field must be a date before or equal to 24 hours.',
            'payment_gateway_id.required' => 'The payment gateway field is required.',
            'payment_gateway_id.exists' => 'The selected payment gateway is invalid.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    $this->test &&
                    $this->user->lastAttendedAdmissionTest &&
                    $this->user->lastAttendedAdmissionTest->testing_at
                        ->addMonths(
                            $this->user->lastAttendedAdmissionTest->type->interval_month
                        )->endOfDay() >= $this->test->testing_at
                ) {
                    $validator->errors()->add(
                        'user_id',
                        "The selected user id has admission test record within {$this->user->lastAttendedAdmissionTest->type->interval_month} months(count from testing at of this test sub {$this->user->lastAttendedAdmissionTest->type->interval_month} months to now)."
                    );
                }
            },
        ];
    }
}
