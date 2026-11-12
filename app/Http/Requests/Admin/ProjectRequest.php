<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use App\Support\TranslatableRules;
use App\Support\VideoEmbed;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
            ...TranslatableRules::for('title', required: true, max: 150),
            ...TranslatableRules::for('summary', max: 300),
            ...TranslatableRules::for('body', max: 10000),
            ...TranslatableRules::for('services_provided', max: 1000),
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('projects', 'slug')->ignore($this->route('project'))],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'client' => ['nullable', 'string', 'max:150'],
            'year' => ['nullable', 'integer', 'between:1990,'.(now()->year + 1)],
            'external_url' => ['nullable', 'url:http,https', 'max:500'],
            'video_url' => ['nullable', 'url:http,https', 'max:500', function (string $attribute, mixed $value, Closure $fail): void {
                if (VideoEmbed::fromUrl($value) === null) {
                    $fail('The :attribute must be a YouTube or Instagram video link.');
                }
            }],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'thumbnail' => ImageRules::upload(),
            'remove_thumbnail' => ['boolean'],
            'images' => ['nullable', 'array', 'max:12'],
            'images.*' => ImageRules::upload(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...TranslatableRules::attributes('title', 'title'),
            ...TranslatableRules::attributes('summary', 'short description'),
            ...TranslatableRules::attributes('body', 'project details'),
            ...TranslatableRules::attributes('services_provided', 'services provided'),
            'category_id' => 'category',
            'external_url' => 'external link',
            'video_url' => 'video link',
            'images.*' => 'gallery image',
        ];
    }

    /**
     * The validated values stored on the project (files are handled separately).
     *
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        return [
            'title' => TranslatableRules::clean($this->validated('title')),
            'summary' => TranslatableRules::clean($this->validated('summary')),
            'body' => TranslatableRules::clean($this->validated('body')),
            'services_provided' => TranslatableRules::clean($this->validated('services_provided')),
            'slug' => $this->validated('slug'),
            'category_id' => $this->validated('category_id'),
            'client' => $this->validated('client'),
            'year' => $this->validated('year'),
            'external_url' => $this->validated('external_url'),
            'video_url' => $this->validated('video_url'),
            'is_published' => $this->boolean('is_published'),
            'is_featured' => $this->boolean('is_featured'),
        ];
    }
}
