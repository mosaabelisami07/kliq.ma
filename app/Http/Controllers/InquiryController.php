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
     * Save a contact form submission and email it to the agency.
     *
     * The visitor only sees the confirmation once the mail server has accepted
     * the email; otherwise they get an error and can send the form again.
     */
    public function store(ContactRequest $request, SiteSettings $settings): JsonResponse|RedirectResponse
    {
        $inquiry = $this->findDuplicate($request) ?? $this->createInquiry($request);

        if (! $this->notify($inquiry, $settings)) {
            $message = __('site.contact.errors.mail_failed');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['form' => [$message]]], 503);
            }

            return back()->withErrors(['form' => $message])->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'title' => __('site.contact.success_title'),
                'message' => __('site.contact.success'),
            ], 201);
        }

        return redirect()->to(route('home').'#contact')->with('inquiry_sent', true);
    }

    private function createInquiry(ContactRequest $request): Inquiry
    {
        $data = $request->safe()->except(['website', 'started_at', 'form_token', 'service']);
        $service = $request->service();

        return Inquiry::query()->create([
            ...$data,
            'service_id' => $service?->id,
            'service_name' => $service?->localized('title', 'en') ?? ($request->validated('service') === 'other' ? 'Other / not sure yet' : null),
            'locale' => app()->getLocale(),
            'submission_token' => $request->validated('form_token'),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ]);
    }

    /**
     * The same form submitted twice, or the same message re-sent within minutes.
     */
    private function findDuplicate(ContactRequest $request): ?Inquiry
    {
        return Inquiry::query()
            ->where('submission_token', $request->validated('form_token'))
            ->orWhere(fn ($query) => $query
                ->where('email', $request->validated('email'))
                ->where('message', $request->validated('message'))
                ->where('created_at', '>=', now()->subMinutes(10)))
            ->first();
    }

    /**
     * Email the agency right away (not queued) so the result is known before
     * answering the visitor. The inquiry is already saved, so a mail failure
     * never loses it; the failure is logged and a retry sends the email again.
     *
     * Returns false only when an email should have gone out and did not.
     */
    private function notify(Inquiry $inquiry, SiteSettings $settings): bool
    {
        if ($inquiry->notified_at || $this->emailIsSentByBrowser()) {
            return true;
        }

        $recipient = $settings->notificationRecipient();

        if (! $recipient) {
            return true;
        }

        try {
            Notification::route('mail', $recipient)->notifyNow(new NewInquiryReceived($inquiry));
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        $inquiry->forceFill(['notified_at' => now()])->save();

        return true;
    }

    /**
     * With WhatsApp or EmailJS delivery the visitor's browser delivers the
     * inquiry (public/js/whatsapp.js, public/js/email.js), so the server must
     * not email it too.
     */
    private function emailIsSentByBrowser(): bool
    {
        return in_array(config('kliq.email_delivery'), ['whatsapp', 'emailjs'], true);
    }
}
