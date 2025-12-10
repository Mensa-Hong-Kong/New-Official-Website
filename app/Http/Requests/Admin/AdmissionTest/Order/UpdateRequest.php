<?php

namespace App\Http\Requests\Admin\AdmissionTest\Order;

use App\Models\AdmissionTestOrder;
use App\Models\OtherPaymentGateway;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        DB::beginTransaction();
        $this->marge([
            'order' => AdmissionTestOrder::lockForUpdate()
                ->withCount('tests')
                ->findOrFail($this->route('order'))
        ]);
    }

    public function rules(): array
    {
        $return['type'] = 'required|string|in:edit,exchange';
        if ($this->type == 'exchange') {
            $return['description'] = 'nullable|string|max:256';
            $return['administrative_charge'] = ['nullable', Rule::numeric()->min(0)->max(99999.99)->decimal(0, 2)];
            $return['amount'] = ['required', Rule::numeric()->min(-$this->order->price)->max(999999.99)->decimal(0, 2)->same($this->order->price + $this->administrative_charge - $this->price)];
            $return['payment_gateway_id'] = ['nullable', 'integer', Rule::exists(OtherPaymentGateway::class, 'id')->where('is_active', true)];
        }
        $return['product_name'] = 'nullable|string|max:255';
        $return['price_name'] = 'nullable|string|max:255';
        $return['price'] = ['required', Rule::numeric()->min(0.01)->max(99999.99)->decimal(0, 2)];
        $return['minimum_age'] = 'nullable|integer|min:1|max:255';
        $return['maximum_age'] = 'nullable|integer|min:1|max:255';
        $return['quota'] = "required|integer|min:{$this->order->tests_count}|max:255";
        $return['reference_number'] = 'nullable|string|max:255';

        return $return;
    }

    public function messages(): array
    {
        return [
            'minimum_age.lt' => 'The minimum age field must be less than maximum age field.',
            'maximum_age.gt' => 'The maximum age field must be greater than minimum age field.',
            'payment_gateway_id.exists' => 'The selected payment gateway is invalid.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $update = [];
                $changingFields = [];
                $ignore = ['type', 'description', 'administrative_charge', 'payment_gateway_id'];
                if ($this->type == 'exchange') {
                    $ignore[] = 'reference_number';
                }
                foreach ($this->validated() as $key => $value) {
                    if (
                        ! in_array($key, $ignore) &&
                        $this->order->$key != $value
                    ) {
                        $update[$key] = $value;
                        $changingFields[] = [
                            'key' => $key,
                            'origin_value' => $this->order->$key,
                            'new_value' => $value,
                        ];
                    }
                }
                if (count($changingFields)) {
                    $validator->errors()->add(
                        'message',
                        'No field has been change.'
                    );
                } else {
                    $this->marge([
                        'update' => $update,
                        'changingFields' => $changingFields,
                    ]);
                }
            }
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        DB::rollBack();
        parent::failedValidation($validator);
    }
}
