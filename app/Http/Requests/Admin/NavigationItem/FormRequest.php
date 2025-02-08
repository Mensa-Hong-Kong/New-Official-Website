<?php

namespace App\Http\Requests\Admin\NavigationItem;

use App\Models\NavigationItem;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [
            'master_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:8000|active_url',
            'display_order' => 'required|integer|min:0',
        ];
        if (! is_array($this->master_id)) {
            if ($this->master_id == 0) {
                $maxDisplayOrder = NavigationItem::whereNull('master_id');
            } else {
                $return['master_id'] .= '|exists:'.NavigationItem::class.',id';
                $maxDisplayOrder = NavigationItem::where('master_id', $this->master_id);
            }
            $maxDisplayOrder = $maxDisplayOrder->max('display_order');
            if ($this->method() == 'POST') {
                if ($maxDisplayOrder !== null) {
                    $maxDisplayOrder++;
                }
            } elseif (
                $maxDisplayOrder !== null &&
                $this->master_id != $this->route('navigation_item')->master_id
            ) {
                $maxDisplayOrder++;
            }
            if ($maxDisplayOrder === null) {
                $maxDisplayOrder = 0;
            }
            $return['display_order'] .= "|max:$maxDisplayOrder";
        }

        return $return;
    }

    public function messages(): array
    {
        return [
            'master_id.required' => 'The master field is required.',
            'master_id.integer' => 'The master field must be an integer.',
            'master_id.exists' => 'The selected master is invalid.',
        ];
    }
}
