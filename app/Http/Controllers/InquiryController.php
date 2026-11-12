<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Inquiry;
use App\Notifications\NewInquiryReceived;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class InquiryController extends Controller
{
    /**
     * Save a contact form submission and notify the agency.
     */
    public function store(ContactRequest $request, SiteSettings $settings): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'started_at', 'form_token', 'service']);

        if (! $this->isDuplicate($request)) {
            $service = $request->service();

            $inquiry = Inquiry::query()->create([
                ...$data,
                'service_id' => $service?->id,
                'service_name' => $service?->localized('title', 'en') ?? ($request->validated('service') === 'other' ? 'Other / not sure yet' : null),
                'locale' => app()->getLocale(),
                'submission_token' => $request->validated('form_token'),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
            ]);

            $this->notify($inquiry, $settings);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'title' => __('site.contact.success_title'),
                'message' => __('site.contact.success'),
            ], 201);
        }

        return redirect()->to(route('home').'#contact')->with('inquiry_sent', true);
    }

    /**
     * The same form submitted twice, or the same message re-sent within minutes.
     */
    private function isDuplicate(ContactRequest $request): bool
    {
        return Inquiry::query()
            ->where('submission_token', $request->validated('form_token'))
            ->orWhere(fn ($query) => $query
                ->where('email', $request->validated('email'))
                ->where('message', $request->validated('message'))
                ->where('created_at', '>=', now()->subMinutes(10)))
            ->exists();
    }

    /**
     * Email the agency. A mail failure must never lose the visitor's inquiry,
     * which is already saved, so errors are reported instead of thrown.
     */
    private function notify(Inquiry $inquiry, SiteSettings $settings): void
    {
        $recipient = $settings->notificationRecipient();

        if (! $recipient) {
            return;
        }

        try {
            Notification::route('mail', $recipient)->notify(new NewInquiryReceived($inquiry));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
