<?php

namespace App\Http\Requests;

use App\Models\AdmissionTestOrder;
use App\Models\AdmissionTestPrice;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->user()->load([
            'lastAdmissionTestOrder' => function($query) {
                $query->withCount('attendedTests');
            }
        ]);
        if(
            ! $this->user()->lastAdmissionTestOrder ||
            $this->user()->lastAdmissionTestOrder->attended_tests_count >= $this->user()->lastAdmissionTestOrder->quota
        ) {
            return ['message' => ['required', 'integer']];
        }

        return [];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                if(
                    ! $this->user()->lastAdmissionTestOrder ||
                    $this->user()->lastAdmissionTestOrder->attended_tests_count >= $this->user()->lastAdmissionTestOrder->quota
                ) {
                    $price = AdmissionTestPrice::find($this->price_id);
                    if (! $price) {
                        $validator->errors()->add(
                            'message',
                            'The selected price id is invalid.'
                        );
                    } else if(
                        $price->product->start_at ?? now() <= now() ||
                        $price->product->end_at ?? now() >= now() || (
                            $price->product->price->id != $price->id &&
                            $price->product->price->price != $price->price
                        )
                    ) {
                        $validator->errors()->add(
                            'message',
                            'The selected price id is invalid.'
                        );
                    } else if(
                        $price->product->minimum_age ?? now() > $this->user()->age ||
                        $price->product->maximum_age ?? now() <= $this->user()->age
                    ) {
                        $validator->errors()->add(
                            'message',
                            'This price is not support your age.'
                        );
                    } else {
                        $this->merge(['price' => $price->product->price]);
                    }
                }
            },
        ];
    }
}
