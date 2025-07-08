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

        return [
            'name' => ['required', 'string', 'max:255', $unique],
            'interval_month' => 'required|integer|min:0|max:60',
            'is_active' => 'required|boolean',
            'display_order' => "required|integer|min:0|max:$maxDisplayOrder",
        ];
    }
}
