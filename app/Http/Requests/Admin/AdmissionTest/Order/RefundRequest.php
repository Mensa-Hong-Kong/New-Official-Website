<?php

namespace App\Http\Requests\Admin\AdmissionTest\Order;

use App\Models\OtherPaymentGateway;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');
        $return = [
            'description' => 'nullable|string|max:256',
            'refund_amount' => ['required', Rule::numeric()->min(-$order->refundableAmount)->max(-0.01)->decimal(0, 2)],
            'administrative_charge' => ['nullable', Rule::numeric()->min(0)->max(99999.99)->decimal(0, 2)],
            'amount' => ['required', Rule::numeric()->min(-$order->refundableAmount)->max(999999.99)->decimal(0, 2)],
            'payment_gateway_id' => ['required', 'integer', Rule::exists(OtherPaymentGateway::class, 'id')->where('is_active', true)],
        ];
        if (! $order->is_returned) {
            $return['is_return'] = 'nullable|boolean';
        }

        return $return;
    }

    public function messages(): array
    {
        return ['payment_gateway_id.exists' => 'The selected payment gateway is invalid.'];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                if ($this->amount != $this->refund_amount + $this->administrative_charge) {
                    $validator->errors()->add(
                        'amount',
                        '...'
                    );
                }
            }
        ];
    }
}
