<?php

namespace App\Http\Requests\Admin\AdmissionTest;

use App\Models\AdmissionTestType;
use Illuminate\Foundation\Http\FormRequest;

class TypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDisplayOrder = AdmissionTestType::max('display_order');
        if ($maxDisplayOrder === null) {
            $maxDisplayOrder = 0;
        } else {
            $maxDisplayOrder++;
        }

        return [
            'name' => 'required|string|max:255|unique:'.AdmissionTestType::class.',name',
            'interval_month' => 'required|integer|min:0|max:60',
            'is_active' => 'required|boolean',
            'display_order' => "required|integer|min:0|max:$maxDisplayOrder",
        ];
    }
}
