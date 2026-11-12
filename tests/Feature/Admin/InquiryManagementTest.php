<?php

namespace Tests\Feature\Admin;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_inquiries_are_listed_with_unread_ones_highlighted(): void
    {
        Inquiry::factory()->create(['name' => 'Unread Client']);
        Inquiry::factory()->status(InquiryStatus::Replied)->create(['name' => 'Read Client']);

        $this->actingAs($this->admin)->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('Unread Client')
            ->assertSee('Read Client')
            ->assertSee('1 unread');

        $this->actingAs($this->admin)->get('/admin')->assertSee('Unread Client');
    }

    public function test_inquiries_can_be_searched_filtered_and_sorted(): void
    {
        $branding = Service::factory()->create();
        Inquiry::factory()->create(['name' => 'Yasmine', 'company' => 'Atlas Tea', 'created_at' => now()->subDays(2)]);
        Inquiry::factory()->create(['name' => 'Omar', 'email' => 'omar@agency.test', 'service_id' => $branding->id, 'created_at' => now()->subDay()]);
        Inquiry::factory()->status(InquiryStatus::Closed)->create(['name' => 'Karim']);

        $this->actingAs($this->admin)->get('/admin/inquiries?q=atlas')->assertSee('Yasmine')->assertDontSee('Omar');
        $this->actingAs($this->admin)->get('/admin/inquiries?q=omar@agency')->assertSee('Omar')->assertDontSee('Yasmine');
        $this->actingAs($this->admin)->get('/admin/inquiries?status=closed')->assertSee('Karim')->assertDontSee('Yasmine');
        $this->actingAs($this->admin)->get("/admin/inquiries?service={$branding->id}")->assertSee('Omar')->assertDontSee('Karim');
        $this->actingAs($this->admin)->get('/admin/inquiries?sort=oldest')->assertSeeInOrder(['Yasmine', 'Omar', 'Karim']);
        $this->actingAs($this->admin)->get('/admin/inquiries')->assertSeeInOrder(['Karim', 'Omar', 'Yasmine']);
    }

    public function test_search_treats_wildcards_literally(): void
    {
        Inquiry::factory()->create(['name' => 'Percent Person']);

        $this->actingAs($this->admin)->get('/admin/inquiries?q=%25')->assertDontSee('Percent Person');
    }

    public function test_opening_an_inquiry_marks_it_as_read(): void
    {
        $inquiry = Inquiry::factory()->create(['phone' => '+212 600-112233']);

        $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertSee("mailto:{$inquiry->email}", false)
            ->assertSee('tel:+212600112233', false);

        $this->assertNotNull($inquiry->fresh()->read_at);
    }

    public function test_status_and_private_notes_can_be_updated(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($this->admin)
            ->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'in_progress', 'notes' => 'Call back on Monday.'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(InquiryStatus::InProgress, $inquiry->fresh()->status);
        $this->assertSame('Call back on Monday.', $inquiry->fresh()->notes);

        $this->actingAs($this->admin)
            ->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'archived'])
            ->assertSessionHasErrors('status');
    }

    public function test_internal_notes_never_reach_the_public_website(): void
    {
        Inquiry::factory()->create(['notes' => 'TOP-SECRET-NOTE']);

        $this->get('/en')->assertDontSee('TOP-SECRET-NOTE');
        $this->assertArrayNotHasKey('notes', Inquiry::query()->first()->toArray());
    }

    public function test_an_inquiry_can_be_deleted(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}")->assertRedirect('/admin/inquiries');

        $this->assertModelMissing($inquiry);
    }

    public function test_inquiries_export_to_csv_with_formulas_neutralised(): void
    {
        Inquiry::factory()->create(['name' => '=HYPERLINK("http://evil.test")', 'company' => 'Atlas']);
        Inquiry::factory()->status(InquiryStatus::Closed)->create(['name' => 'Closed One']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries/export?status=new');

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('Atlas', $csv);
        $this->assertStringNotContainsString('Closed One', $csv);
    }

    public function test_dashboard_statistics_come_from_the_database(): void
    {
        Inquiry::factory()->count(3)->create();
        Inquiry::factory()->status(InquiryStatus::InProgress)->create();

        $this->actingAs($this->admin)->get('/admin')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 4 && $stats['new'] === 3 && $stats['in_progress'] === 1);
    }
}
