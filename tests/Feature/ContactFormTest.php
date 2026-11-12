<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\Service;
use App\Notifications\NewInquiryReceived;
use App\Support\SiteSettings;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Amina Benali',
            'company' => 'Atlas Tea',
            'email' => 'amina@example.com',
            'phone' => '+212 600 11 22 33',
            'budget' => '20-50k',
            'preferred_contact' => 'whatsapp',
            'message' => 'We are launching a new brand of Moroccan tea this spring.',
            'started_at' => Crypt::encryptString((string) now()->subMinute()->timestamp),
            'form_token' => (string) Str::uuid(),
            ...$overrides,
        ];
    }

    public function test_an_inquiry_is_saved_and_the_visitor_sees_a_confirmation(): void
    {
        $service = Service::factory()->create(['title' => ['en' => 'Branding']]);

        $this->post('/fr/contact', $this->payload(['service' => (string) $service->id]))
            ->assertRedirect('/fr#contact')
            ->assertSessionHas('inquiry_sent');

        $inquiry = Inquiry::query()->sole();
        $this->assertSame('Amina Benali', $inquiry->name);
        $this->assertSame($service->id, $inquiry->service_id);
        $this->assertSame('Branding', $inquiry->service_name);
        $this->assertSame(InquiryStatus::New, $inquiry->status);
        $this->assertSame('fr', $inquiry->locale);
        $this->assertNull($inquiry->read_at);

        $this->get('/fr')->assertSee(__('site.contact.success_title', [], 'fr'));
    }

    public function test_javascript_submissions_get_a_json_response(): void
    {
        $this->postJson('/en/contact', $this->payload(['service' => 'other']))
            ->assertCreated()
            ->assertJson(['title' => __('site.contact.success_title')]);

        $this->assertSame('Other / not sure yet', Inquiry::query()->sole()->service_name);
    }

    public function test_the_agency_is_emailed_when_notifications_are_enabled(): void
    {
        Notification::fake();
        app(SiteSettings::class)->save(['notification_email' => 'team@example.test', 'notify_on_inquiry' => true]);

        $this->post('/en/contact', $this->payload());

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewInquiryReceived::class,
            fn (NewInquiryReceived $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'team@example.test',
        );
    }

    public function test_the_email_contains_the_full_escaped_message(): void
    {
        $message = str_repeat('We need a full launch campaign. ', 40)."\nSecond paragraph <script>alert(1)</script>";
        $inquiry = Inquiry::factory()->create(['message' => $message]);

        $html = (string) (new NewInquiryReceived($inquiry))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString('Second paragraph &lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertSame(40, substr_count($html, 'We need a full launch campaign.'));
    }

    public function test_no_email_is_sent_when_notifications_are_disabled(): void
    {
        Notification::fake();
        app(SiteSettings::class)->save(['notification_email' => 'team@example.test', 'notify_on_inquiry' => false]);

        $this->post('/en/contact', $this->payload());

        Notification::assertNothingSent();
        $this->assertDatabaseCount('inquiries', 1);
    }

    public function test_validation_errors_are_translated(): void
    {
        $this->from('/ar')
            ->post('/ar/contact', $this->payload(['name' => '', 'email' => 'not-an-email', 'message' => 'Short']))
            ->assertRedirect('/ar')
            ->assertSessionHasErrors([
                'name' => __('site.contact.errors.name_required', [], 'ar'),
                'email' => __('site.contact.errors.email_invalid', [], 'ar'),
                'message' => __('site.contact.errors.message_min', [], 'ar'),
            ]);

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_json_validation_errors_are_returned_per_field(): void
    {
        $this->postJson('/en/contact', $this->payload(['phone' => 'call me maybe', 'service' => '999']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone', 'service']);
    }

    public function test_inactive_services_cannot_be_requested(): void
    {
        $service = Service::factory()->inactive()->create();

        $this->postJson('/en/contact', $this->payload(['service' => (string) $service->id]))
            ->assertJsonValidationErrors('service');
    }

    public function test_the_honeypot_rejects_bots(): void
    {
        $this->post('/en/contact', $this->payload(['website' => 'http://spam.test']))->assertSessionHasErrors('website');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_instant_submissions_are_rejected(): void
    {
        $this->postJson('/en/contact', $this->payload(['started_at' => Crypt::encryptString((string) now()->timestamp)]))
            ->assertJsonValidationErrors(['form' => __('site.contact.errors.too_fast')]);
    }

    public function test_a_tampered_timestamp_is_rejected(): void
    {
        $this->postJson('/en/contact', $this->payload(['started_at' => '1']))->assertJsonValidationErrors('form');
    }

    public function test_submitting_the_same_form_twice_saves_one_inquiry(): void
    {
        $payload = $this->payload();

        $this->postJson('/en/contact', $payload)->assertCreated();
        $this->postJson('/en/contact', $payload)->assertCreated();
        $this->postJson('/en/contact', [...$payload, 'form_token' => (string) Str::uuid()])->assertCreated();

        $this->assertDatabaseCount('inquiries', 1);
    }

    public function test_submissions_are_rate_limited(): void
    {
        foreach (range(1, 3) as $attempt) {
            $this->postJson('/en/contact', $this->payload(['message' => "Attempt {$attempt}: we need a new brand identity soon."]))->assertCreated();
        }

        $this->postJson('/en/contact', $this->payload())->assertTooManyRequests();
    }

    public function test_the_form_requires_a_csrf_token(): void
    {
        $router = app('router');

        $this->assertContains('web', $router->getRoutes()->getByName('contact.store')->gatherMiddleware());
        $this->assertContains(PreventRequestForgery::class, app(Kernel::class)->getMiddlewareGroups()['web']);
    }
}
