<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'url:http,https', 'max:500'],
            'is_active' => ['boolean'],
            'logo' => ImageRules::upload(2048),
            'remove_logo' => ['boolean'],
        ];
    }

    /**
     * @return array{name: string, url: string|null, is_active: bool}
     */
    public function brandAttributes(): array
    {
        return [
            'name' => $this->validated('name'),
            'url' => $this->validated('url'),
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}
