<?php

namespace App\Http\Requests\Admin;

use App\Support\Icons;
use App\Support\ImageRules;
use App\Support\TranslatableRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
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
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('title.'.config('app.fallback_locale')))),
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
            ...TranslatableRules::for('title', required: true, max: 120),
            ...TranslatableRules::for('summary', max: 300),
            ...TranslatableRules::for('body', max: 10000),
            ...TranslatableRules::for('deliverables', max: 1000),
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'icon' => ['nullable', Rule::in(Icons::choices())],
            'image' => ImageRules::upload(),
            'remove_image' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...TranslatableRules::attributes('title', 'title'),
            ...TranslatableRules::attributes('summary', 'description'),
            ...TranslatableRules::attributes('body', 'details'),
            ...TranslatableRules::attributes('deliverables', 'included items'),
        ];
    }

    /**
     * The validated values stored on the service (the image is handled separately).
     *
     * @return array<string, mixed>
     */
    public function serviceAttributes(): array
    {
        return [
            'title' => TranslatableRules::clean($this->validated('title')),
            'summary' => TranslatableRules::clean($this->validated('summary')),
            'body' => TranslatableRules::clean($this->validated('body')),
            'deliverables' => TranslatableRules::clean($this->validated('deliverables')),
            'slug' => $this->validated('slug'),
            'icon' => $this->validated('icon'),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
