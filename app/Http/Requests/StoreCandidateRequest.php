<?php

namespace App\Http\Requests;

use App\Models\AdmissionTestPrice;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [];

        if (
            ! $this->route('admission_test')->is_free &&
            ! $this->user()->hasUnusedQuotaAdmissionTestOrder
        ) {
            $request = $this;
            $return = [
                'price_id' => [
                    'required', 'integer', 
                    function (string $attribute, mixed $value, Closure $fail) use ($request) {
                        $price = AdmissionTestPrice::with('product.price')
                            ->find($value);
                        if (! $price) {
                            $fail('The selected product is invalid.');
                        } elseif ($price->price != $price->product->price->price) {
                            $fail('The price of selected product is not up to date, please try again on this up to date version.');
                        } elseif ($price->product->start_at && $price->product->start_at > now()) {
                            $fail('The selected product is not yet released, please try again later or select other product.');
                        } elseif ($price->product->end_at && $price->product->end_at < now()) {
                            $fail('The selected product was taken down, please select other product.');
                        } elseif ($price->product->minimum_age && $price->product->minimum_age > floor($request->user()->age)) {
                            $fail('Your age less than product minimum age limit.');
                        } elseif ($price->product->maximum_age && $price->product->maximum_age < floor($request->user()->age)) {
                            $fail('Your age greater than product maximum age limit.');
                        } else {
                            $request->merge(['product' => $price->product]);
                        }
                    }
                ]
            ];
        }

        return $return;
    }
}
