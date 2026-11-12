<?php

namespace App\Http\Requests\Admin;

use App\Support\TranslatableRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name.'.config('app.fallback_locale')))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...TranslatableRules::for('name', required: true, max: 60),
            'slug' => ['required', 'alpha_dash', 'max:60', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return TranslatableRules::attributes('name', 'name');
    }

    /**
     * @return array{name: array<string, string>, slug: string}
     */
    public function categoryAttributes(): array
    {
        return [
            'name' => TranslatableRules::clean($this->validated('name')),
            'slug' => $this->validated('slug'),
        ];
    }
}
