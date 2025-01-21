<?php

namespace App\Http\Requests\Admin\Role;

use App\Models\ModulePermission;
use App\Models\Role;
use App\Models\TeamRole;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FormRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $team = $this->route('team');
        $maxDisplayOrder = TeamRole::where('team_id', $team->id)
            ->max('display_order') + 1;
        $unique = Rule::unique(Role::class, 'name')
            ->whereIn(
                'id', $team->roles
                    ->pluck('id')
                    ->toArray()
            );
        $modulePermissionIDs = ModulePermission::get('id');
        $modulePermissionIDs = $modulePermissionIDs->implode('id', ',');

        return [
            'name' => ['required', 'string', 'max:170', 'regex:/^(?!.*:).*$/', $unique],
            'display_order' => "required|integer|min:0|max:$maxDisplayOrder",
            'module_permissions' => 'present|array',
            'module_permissions.*' => "required|integer|distinct|in:$modulePermissionIDs",
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The name field cannot has ":".',
            'name.unique' => 'The name of role in this team has already been taken.',
            'module_permissions.present' => 'The permissions field must be present, if you are using our CMS, please contact I.T. officer.',
            'module_permissions.array' => 'The permissions field must be an array, if you are using our CMS, please contact I.T. officer.',
            'module_permissions.*.integer' => 'The value of permissions must be an integer, if you are using our CMS, please contact I.T. officer.',
            'module_permissions.*.distinct' => 'The permissions has a duplicate value, if you are using our CMS, please contact I.T. officer.',
            'module_permissions.*.in' => 'The permissions not up to date, if you are using our CMS, please refresh. If the problem persists, please contact I.T. officer.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $keys = $errors->keys();
        $messages = [];
        foreach ($keys as $key) {
            $message = $errors->first($key);
            if (str_starts_with($key, 'module_permissions')) {
                $key = 'message';
            }
            if (! isset($messages[$key])) {
                $messages[$key] = $message;
            }
        }

        throw ValidationException::withMessages($messages);
    }
}
