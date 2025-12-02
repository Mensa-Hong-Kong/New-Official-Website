<?php

namespace App\Http\Requests\Admin\AdmissionTest\Type;

use App\Models\AdmissionTestType;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Validation\Rule;

class FormRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDisplayOrder = AdmissionTestType::max('display_order');
        $this->merge(['maxDisplayOrder' => $maxDisplayOrder ?? 0]);
        $unique = Rule::unique(AdmissionTestType::class);
        if ($this->method() != 'POST') {
            $unique = $unique->ignore($this->route('type')->id);
        }
        if ($maxDisplayOrder === null) {
            $maxDisplayOrder = 0;
        } elseif ($this->method() == 'POST') {
            $maxDisplayOrder++;
        }
        $return = [
            'name' => ['required', 'string', 'max:255', $unique],
            'interval_month' => 'required|integer|min:0|max:60',
            'minimum_age' => 'nullable|integer|min:1|max:255',
            'maximum_age' => 'nullable|integer|min:1|max:255',
            'is_active' => 'required|boolean',
            'display_order' => "required|integer|min:0|max:$maxDisplayOrder",
        ];
        if ($this->minimum_age && $this->maximum_age) {
            $return['minimum_age'] .= '|lt:maximum_age';
            $return['maximum_age'] .= '|gt:minimum_age';
        }

        return $return;
    }

    public function messages(): array
    {
        return [
            'minimum_age.lt' => 'The minimum age field must be less than maximum age field.',
            'maximum_age.gt' => 'The maximum age field must be greater than minimum age field.',
        ];
    }
}
