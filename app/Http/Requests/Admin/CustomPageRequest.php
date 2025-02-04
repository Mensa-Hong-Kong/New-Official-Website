<?php

namespace App\Http\Requests\Admin;

use App\Models\CustomPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique(CustomPage::class);

        return [
            'pathname' => ['required', 'string', 'max:768', 'regex:/^[A-Za-z0-9-\/]+$/', $unique],
            'title' => 'required|string|max:60',
            'og_image_url' => 'nullable|string|max:15484|active_url',
            'description' => 'required|string|max:65',
            'content' => 'nullable|string|max:4194303',
        ];
    }

    public function messages(): array
    {
        return [
            'pathname.regex' => 'The pathname field must only contain letters, numbers, dashes and slash.',
            'og_image_url.string' => 'The open graph image url field must be a string.',
            'og_image_url.max' => 'The open graph image url field must not be greater than 15484 characters.',
            'og_image_url.active_url' => 'The open graph image url field is not a valid URL.',
        ];
    }
}
