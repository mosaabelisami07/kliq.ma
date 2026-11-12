<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageRules;
use App\Support\TranslatableRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    /**
     * Translatable settings edited on this page.
     */
    public const TEXT_KEYS = ['address', 'seo_title', 'seo_description'];

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
        $phone = 'regex:/^\+?[\d\s().-]{6,25}$/';

        return [
            ...TranslatableRules::for('address', max: 255),
            ...TranslatableRules::for('seo_title', max: 70),
            ...TranslatableRules::for('seo_description', max: 170),
            'contact_email' => ['nullable', 'email:rfc', 'max:190'],
            'contact_phone' => ['nullable', 'string', $phone],
            'whatsapp' => ['nullable', 'string', $phone],
            'map_url' => ['nullable', 'url:https', 'max:500'],
            'map_location' => ['nullable', 'string', 'max:200'],
            'notification_email' => ['nullable', 'email:rfc', 'max:190'],
            'notify_on_inquiry' => ['boolean'],
            'socials' => ['nullable', 'array'],
            'socials.*' => ['nullable', 'url:https,http', 'max:300'],
            'logo' => ImageRules::upload(2048),
            'favicon' => ImageRules::favicon(),
            'og_image' => ImageRules::upload(4096),
            'remove_logo' => ['boolean'],
            'remove_favicon' => ['boolean'],
            'remove_og_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...TranslatableRules::attributes('seo_title', 'SEO title'),
            ...TranslatableRules::attributes('seo_description', 'meta description'),
            'og_image' => 'social sharing image',
            'socials.*' => 'social link',
        ];
    }

    /**
     * Validated text values ready to be saved as settings (files excluded).
     *
     * @return array<string, mixed>
     */
    public function settingValues(): array
    {
        $values = [];

        foreach (self::TEXT_KEYS as $key) {
            $values[$key] = TranslatableRules::clean($this->validated($key));
        }

        foreach (config('kliq.settings.strings') as $key) {
            $values[$key] = $this->validated($key);
        }

        $values['notify_on_inquiry'] = $this->boolean('notify_on_inquiry');
        $values['socials'] = collect($this->validated('socials') ?? [])
            ->only(array_keys(config('kliq.settings.socials')))
            ->filter()
            ->all();

        return $values;
    }
}
