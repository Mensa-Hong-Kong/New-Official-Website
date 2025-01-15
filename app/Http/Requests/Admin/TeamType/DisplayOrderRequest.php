<?php

namespace App\Http\Requests\Admin\TeamType;

use App\Models\TeamType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DisplayOrderRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $IDs = TeamType::get('id')
            ->pluck('id')
            ->toArray();
        $size = count($IDs);
        $IDs = implode(',', $IDs);

        return [
            'display_order' => "required|array|size:$size",
            'display_order.*' => "required|integer|distinct|in:$IDs",
        ];
    }

    public function messages(): array
    {
        return [
            'display_order.size' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
            'display_order.*.in' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if ($message != 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.') {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
