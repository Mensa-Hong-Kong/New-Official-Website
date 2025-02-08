<?php

namespace App\Http\Requests\Admin\Team;

use App\Models\Team;
use App\Models\TeamType;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Validation\Rule;

class FormRequest extends BaseFormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $return = [
            'name' => ['required', 'string', 'max:170', 'regex:/^(?!.*:).*$/'],
            'type_id' => 'required|integer|exists:'.TeamType::class.',id',
            'display_order' => 'required|integer|min:0',
        ];
        if (! is_array($this->type_id)) {
            $unique = Rule::unique(Team::class, 'name')
                ->where('type_id', $this->type_id);
            $maxDisplayOrder = Team::where('type_id', $this->type_id)
                ->max('display_order');
            if ($this->method() == 'POST') {
                if ($maxDisplayOrder !== null) {
                    $maxDisplayOrder++;
                }
            } else {
                if (
                    $maxDisplayOrder !== null &&
                    $this->type_id != $this->route('team')->type_id
                ) {
                    $maxDisplayOrder++;
                }
                $unique = $unique->ignore($this->route('team'));
            }
            if ($maxDisplayOrder === null) {
                $maxDisplayOrder = 0;
            }
            $return['name'][] = $unique;
            $return['display_order'] .= "|max:$maxDisplayOrder";
        }

        return $return;
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The name field cannot has ":".',
            'name.unique' => 'The name of team in this type has already been taken.',
            'type_id.required' => 'The type field is required.',
            'type_id.integer' => 'The type field must be an integer.',
            'type_id.exists' => 'The selected type is invalid.',
        ];
    }
}
