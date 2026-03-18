<?php

namespace App\Http\Requests\Admin\AdmissionTest\Candidate;

use Illuminate\Contracts\Validation\Validator;
use App\Models\AdmissionTestHasCandidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SeatNumbersRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $IDs = AdmissionTestHasCandidate::where('test_id', $this->route('admission_test')->id)
            ->get('user_id');
        $size = $IDs->count();
        $IDs = $IDs->implode('user_id', ',');

        return [
            'seat_numbers' => "required|array|size:$size",
            'seat_numbers.*' => "required|integer|distinct|in:$IDs",
        ];
    }

    public function messages(): array
    {
        return [
            'seat_numbers.size' => 'The ID(s) of seat numbers field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
            'seat_numbers.*.in' => 'The ID(s) of seat numbers field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if ($message != 'The ID(s) of seat numbers field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.') {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
