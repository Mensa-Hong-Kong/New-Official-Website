<?php

namespace App\Http\Requests\Admin\Team;

use App\Models\Team;
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
        $return = [
            'type_id' => 'required|integer|exists:'.TeamType::class.',id',
            'display_order' => 'required|array',
            'display_order.*' => 'required|integer|distinct',
        ];
        if (! is_array($this->type_id)) {
            $IDs = Team::where('type_id', $this->type_id)
                ->get('id')
                ->pluck('id')
                ->toArray();
            $size = count($IDs);
            $IDs = implode(',', $IDs);
            $return['display_order'] .= "|size:$size";
            $return['display_order.*'] .= "|in:$IDs";
        }

        return $return;
    }

    public function messages(): array
    {
        return [
            'type_id.required' => 'The type field is required, if you are using our CMS, please contact I.T. officer.',
            'type_id.integer' => 'The type field must be an integer, if you are using our CMS, please contact I.T. officer.',
            'type_id.exists' => 'The selected type is invalid, if you are using our CMS, please contact I.T. officer.',
            'display_order.size' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
            'display_order.*.in' => 'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $message = $errors->first();
        $key = 'message';
        if (
            ! in_array(
                $message,
                [
                    'The type field is required, if you are using our CMS, please contact I.T. officer.',
                    'The type field must be an integer, if you are using our CMS, please contact I.T. officer.',
                    'The selected type is invalid, if you are using our CMS, please contact I.T. officer.',
                    'The ID(s) of display order field is not up to date, it you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
                ]
            )
        ) {
            $key = $errors->keys()[0];
        }

        throw ValidationException::withMessages([$key => $message]);
    }
}
