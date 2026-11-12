<?php

namespace App\Http\Requests\Admin;

use App\Support\Icons;
use App\Support\TranslatableRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentRequest extends FormRequest
{
    /**
     * Translatable texts edited on the "Website content" page, by section.
     */
    public const SECTIONS = [
        'Hero' => ['hero_eyebrow', 'hero_title', 'hero_text'],
        'Brands' => ['brands_title'],
        'About' => ['about_title', 'about_text', 'about_mission', 'about_approach'],
        'Services' => ['services_title', 'services_text'],
        'Our work' => ['work_title', 'work_text'],
        'Process' => ['process_title', 'process_text'],
        'Why choose KLIQ' => ['why_title', 'why_text'],
        'Contact' => ['contact_title', 'contact_text'],
        'Footer' => ['footer_text'],
    ];

    /**
     * Repeating lists, and the section each one belongs to.
     */
    public const LISTS = [
        'values' => 'About',
        'process_steps' => 'Process',
        'why_items' => 'Why choose KLIQ',
    ];

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
        $rules = [];

        foreach (self::textKeys() as $key) {
            $rules += TranslatableRules::for($key, required: $key === 'hero_title', max: 2000);
        }

        foreach (array_keys(self::LISTS) as $list) {
            $rules[$list] = ['nullable', 'array', 'max:'.config("kliq.settings.lists.{$list}.rows")];
            $rules["{$list}.*.icon"] = ['nullable', Rule::in(Icons::choices())];
            $rules["{$list}.*.title"] = ['nullable', 'array'];
            $rules["{$list}.*.title.*"] = ['nullable', 'string', 'max:120'];
            $rules["{$list}.*.text"] = ['nullable', 'array'];
            $rules["{$list}.*.text.*"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return TranslatableRules::attributes('hero_title', 'hero heading');
    }

    /**
     * @return list<string>
     */
    public static function textKeys(): array
    {
        return array_merge(...array_values(self::SECTIONS));
    }

    /**
     * Validated values ready to be saved as settings.
     *
     * @return array<string, mixed>
     */
    public function settingValues(): array
    {
        $values = [];

        foreach (self::textKeys() as $key) {
            $values[$key] = TranslatableRules::clean($this->validated($key));
        }

        foreach (array_keys(self::LISTS) as $list) {
            $values[$list] = collect($this->validated($list) ?? [])
                ->map(fn (array $item): array => [
                    'icon' => $item['icon'] ?? 'sparkles',
                    'title' => TranslatableRules::clean($item['title'] ?? []),
                    'text' => TranslatableRules::clean($item['text'] ?? []),
                ])
                ->values()
                ->all();
        }

        return $values;
    }
}
