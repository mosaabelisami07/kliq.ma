<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContactRequest extends FormRequest
{
    /**
     * Submissions sent faster than this after the form loaded are treated as bots.
     */
    public const MINIMUM_SECONDS = 3;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * "website" is a honeypot real visitors never see; "started_at" is the
     * encrypted time the form was rendered; "form_token" is a per-render id
     * that makes double submissions of the same form idempotent.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\+?[\d\s().-]{6,25}$/'],
            'service' => ['nullable', Rule::in(['other', ...Service::query()->where('is_active', true)->pluck('id')->map(fn (int $id): string => (string) $id)])],
            'budget' => ['nullable', Rule::in(config('kliq.budgets'))],
            'preferred_contact' => ['nullable', Rule::in(config('kliq.contact_methods'))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'website' => ['prohibited'],
            'started_at' => ['required', 'string'],
            'form_token' => ['required', 'string', 'uuid'],
        ];
    }

    /**
     * Reject forms that were submitted implausibly fast or tampered with.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('started_at')) {
                    return;
                }

                try {
                    $startedAt = (int) Crypt::decryptString((string) $this->input('started_at'));
                } catch (DecryptException) {
                    $validator->errors()->add('form', __('site.contact.errors.generic'));

                    return;
                }

                if (now()->timestamp - $startedAt < self::MINIMUM_SECONDS) {
                    $validator->errors()->add('form', __('site.contact.errors.too_fast'));
                }
            },
        ];
    }

    /**
     * Get the translated messages for the fields visitors fill in.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('site.contact.errors.name_required'),
            'email.required' => __('site.contact.errors.email_required'),
            'email.email' => __('site.contact.errors.email_invalid'),
            'phone.regex' => __('site.contact.errors.phone_invalid'),
            'service.in' => __('site.contact.errors.service_invalid'),
            'message.required' => __('site.contact.errors.message_required'),
            'message.min' => __('site.contact.errors.message_min'),
            'started_at.required' => __('site.contact.errors.generic'),
            'form_token.*' => __('site.contact.errors.generic'),
        ];
    }

    /**
     * The chosen service, when it is one of the active services.
     */
    public function service(): ?Service
    {
        $service = $this->validated('service');

        return $service && $service !== 'other' ? Service::query()->find($service) : null;
    }
}
