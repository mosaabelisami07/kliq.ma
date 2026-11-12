<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_project_can_be_created_with_images(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => ['en' => 'Summer Launch', 'fr' => 'Lancement d’été'],
            'summary' => ['en' => 'A seasonal campaign.'],
            'services_provided' => ['en' => "Strategy\nContent"],
            'client' => 'Atlas Tea',
            'category_id' => $category->id,
            'external_url' => 'https://example.com/case',
            'is_published' => '1',
            'thumbnail' => UploadedFile::fake()->image('cover.jpg', 1200, 900),
            'images' => [UploadedFile::fake()->image('one.png'), UploadedFile::fake()->image('two.webp')],
        ])->assertRedirect('/admin/projects/summer-launch/edit');

        $project = Project::query()->with('images')->sole();
        $this->assertSame('summer-launch', $project->slug);
        $this->assertSame('Lancement d’été', $project->localized('title', 'fr'));
        $this->assertSame(['Strategy', 'Content'], $project->localizedLines('services_provided'));
        Storage::disk('public')->assertExists($project->thumbnail);
        $this->assertCount(2, $project->images);
        Storage::disk('public')->assertExists($project->images->first()->path);

        $this->get('/en/projects/summer-launch')->assertOk()->assertSee(Storage::disk('public')->url($project->thumbnail), false);
    }

    public function test_a_project_video_link_plays_on_the_project_page(): void
    {
        $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => ['en' => 'Brand Film'],
            'video_url' => 'https://www.youtube.com/watch?v=tbvsW-nLytA&t=12s',
            'is_published' => '1',
        ])->assertSessionHasNoErrors();

        $this->get('/en/projects/brand-film')
            ->assertOk()
            ->assertSee('src="https://www.youtube-nocookie.com/embed/tbvsW-nLytA?rel=0"', false);

        Project::query()->sole()->update(['video_url' => 'https://www.instagram.com/reel/DYLM1UgoxEn/?cplk=abc']);

        $this->get('/en/projects/brand-film')->assertSee('src="https://www.instagram.com/reel/DYLM1UgoxEn/embed/"', false);
    }

    public function test_a_project_video_link_must_be_youtube_or_instagram(): void
    {
        foreach (['https://vimeo.com/123456', 'javascript:alert(1)', 'https://evil.test/watch?v=tbvsW-nLytA', 'https://youtube.com.evil.test/watch?v=tbvsW-nLytA'] as $url) {
            $this->actingAs($this->admin)->post('/admin/projects', ['title' => ['en' => 'Bad Video'], 'video_url' => $url])->assertSessionHasErrors('video_url');
        }

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_uploads_must_be_real_raster_images(): void
    {
        $base = ['title' => ['en' => 'Bad Upload']];

        foreach ([
            UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
            UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->create('fake.jpg', 10, 'text/plain'),
            UploadedFile::fake()->image('huge.jpg')->size(6000),
        ] as $file) {
            $this->actingAs($this->admin)->post('/admin/projects', [...$base, 'thumbnail' => $file])->assertSessionHasErrors('thumbnail');
        }

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_project_thumbnail_is_replaced_and_old_files_are_removed(): void
    {
        Storage::disk('public')->put('projects/old.jpg', 'old');
        $project = Project::factory()->create(['thumbnail' => 'projects/old.jpg']);

        $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}", [
            'title' => ['en' => $project->localized('title')],
            'slug' => $project->slug,
            'thumbnail' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('projects/old.jpg');
        Storage::disk('public')->assertExists($project->fresh()->thumbnail);
    }

    public function test_projects_can_be_published_reordered_and_deleted(): void
    {
        $first = Project::factory()->create(['sort_order' => 0]);
        $second = Project::factory()->create(['sort_order' => 1]);
        Storage::disk('public')->put('projects/gallery.jpg', 'x');
        $second->images()->create(['path' => 'projects/gallery.jpg']);

        $this->actingAs($this->admin)->patch("/admin/projects/{$first->slug}/publish");
        $this->assertFalse($first->fresh()->is_published);

        $this->actingAs($this->admin)->patch("/admin/projects/{$second->slug}/move", ['direction' => 'up']);
        $this->assertSame([$second->id, $first->id], Project::query()->orderBy('sort_order')->pluck('id')->all());

        $this->actingAs($this->admin)->delete("/admin/projects/{$second->slug}")->assertRedirect('/admin/projects');
        $this->assertModelMissing($second);
        Storage::disk('public')->assertMissing('projects/gallery.jpg');
    }

    public function test_a_gallery_image_can_only_be_removed_from_its_own_project(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $image = $other->images()->create(['path' => 'projects/x.jpg']);

        $this->actingAs($this->admin)->delete("/admin/projects/{$project->slug}/images/{$image->id}")->assertNotFound();
        $this->actingAs($this->admin)->delete("/admin/projects/{$other->slug}/images/{$image->id}")->assertRedirect();

        $this->assertModelMissing($image);
    }

    public function test_services_can_be_managed(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', [
            'title' => ['en' => 'Podcast Production'],
            'summary' => ['en' => 'Audio shows for brands.'],
            'icon' => 'video',
            'is_active' => '1',
        ])->assertRedirect('/admin/services/podcast-production/edit');

        $service = Service::query()->sole();
        $this->get('/en')->assertSee('Podcast Production');

        $this->actingAs($this->admin)->patch("/admin/services/{$service->slug}/toggle");
        $this->assertFalse($service->fresh()->is_active);
        $this->get('/en')->assertDontSee('Podcast Production');

        $this->actingAs($this->admin)->post('/admin/services', ['title' => ['en' => 'X'], 'icon' => 'not-an-icon'])->assertSessionHasErrors('icon');
    }

    public function test_deleting_a_service_keeps_its_name_on_past_inquiries(): void
    {
        $service = Service::factory()->create(['title' => ['en' => 'Branding']]);
        $inquiry = Inquiry::factory()->create(['service_id' => $service->id, 'service_name' => 'Branding']);

        $this->actingAs($this->admin)->delete("/admin/services/{$service->slug}")->assertRedirect('/admin/services');

        $this->assertNull($inquiry->fresh()->service_id);
        $this->assertSame('Branding', $inquiry->fresh()->serviceLabel());
    }

    public function test_categories_can_be_managed(): void
    {
        $this->actingAs($this->admin)->post('/admin/categories', ['name' => ['en' => 'Social Media', 'ar' => 'وسائل التواصل']])->assertSessionHasNoErrors();

        $category = Category::query()->sole();
        $this->assertSame('social-media', $category->slug);

        $project = Project::factory()->for($category)->create();
        $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $this->assertModelMissing($category);
        $this->assertNull($project->fresh()->category_id);
    }

    public function test_website_content_can_be_edited(): void
    {
        $this->actingAs($this->admin)->put('/admin/content', [
            'hero_title' => ['en' => 'We make brands *click*', 'fr' => 'Nous faisons *cliquer* les marques'],
            'about_text' => ['en' => 'An edited about text.'],
            'process_steps' => [
                ['icon' => 'search', 'title' => ['en' => 'Listen'], 'text' => ['en' => 'We start with questions.']],
                ['icon' => 'rocket', 'title' => ['en' => ''], 'text' => ['en' => '']],
            ],
        ])->assertSessionHasNoErrors();

        $this->get('/en')
            ->assertSee('We make brands <span class="text-orange">click</span>', false)
            ->assertSee('An edited about text.')
            ->assertSee('We start with questions.')
            ->assertDontSee('Strategize');
        $this->get('/fr')->assertSee('Nous faisons <span class="text-orange">cliquer</span> les marques', false);

        $this->actingAs($this->admin)->put('/admin/content', ['hero_title' => ['en' => '']])->assertSessionHasErrors('hero_title.en');
    }

    public function test_settings_and_branding_can_be_updated(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'contact_email' => 'hello@kliq.test',
            'whatsapp' => '+212 6 00 00 00 00',
            'notification_email' => 'team@kliq.test',
            'notify_on_inquiry' => '1',
            'socials' => ['instagram' => 'https://instagram.com/kliq', 'tiktok' => ''],
            'seo_title' => ['en' => 'KLIQ Agency'],
            'logo' => UploadedFile::fake()->image('logo.png', 400, 120),
        ])->assertSessionHasNoErrors();

        $this->get('/en')
            ->assertSee('<title>KLIQ Agency</title>', false)
            ->assertSee('mailto:hello@kliq.test', false)
            ->assertSee('https://wa.me/212600000000', false)
            ->assertSee('https://instagram.com/kliq', false)
            ->assertDontSee('TikTok');

        $this->actingAs($this->admin)->put('/admin/settings', ['socials' => ['instagram' => 'javascript:alert(1)']])->assertSessionHasErrors('socials.instagram');
    }
}
