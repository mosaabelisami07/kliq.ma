<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function localesProvider(): array
    {
        return [
            'arabic' => ['ar', 'rtl'],
            'french' => ['fr', 'ltr'],
            'english' => ['en', 'ltr'],
        ];
    }

    public function test_root_redirects_to_the_browser_language(): void
    {
        $this->get('/', ['Accept-Language' => 'fr-FR,fr;q=0.9'])->assertRedirect('/fr');
    }

    public function test_root_falls_back_to_the_default_locale(): void
    {
        $this->get('/', ['Accept-Language' => 'de-DE'])->assertRedirect('/'.config('app.locale'));
    }

    public function test_root_remembers_the_last_visited_locale(): void
    {
        $this->withSession(['locale' => 'ar'])->get('/')->assertRedirect('/ar');
    }

    #[DataProvider('localesProvider')]
    public function test_every_page_renders_in_every_locale(string $locale, string $direction): void
    {
        $project = Project::factory()->create();
        $service = Service::factory()->create();

        foreach (['', '/projects', "/projects/{$project->slug}", "/services/{$service->slug}"] as $path) {
            $this->get("/{$locale}{$path}")
                ->assertOk()
                ->assertSee("lang=\"{$locale}\" dir=\"{$direction}\"", false)
                ->assertSee(__('site.nav.about', [], $locale));
        }
    }

    public function test_home_page_has_every_section(): void
    {
        Service::factory()->create();

        $response = $this->get('/en')->assertOk();

        foreach (['about', 'services', 'work', 'process', 'why', 'contact'] as $section) {
            $response->assertSee("id=\"{$section}\"", false);
        }

        $response->assertSee('Creative Ideas. Strategic Marketing.')
            ->assertSee('<span class="text-orange">Real Growth.</span>', false);
    }

    public function test_default_copy_is_translated(): void
    {
        $this->get('/fr')->assertSee('Idées créatives.');
        $this->get('/ar')->assertSee('أفكار مبدعة.');
    }

    public function test_translated_content_falls_back_to_english(): void
    {
        Service::factory()->create(['title' => ['en' => 'Brand Strategy', 'fr' => 'Stratégie de marque']]);

        $this->get('/fr')->assertSee('Stratégie de marque');
        $this->get('/ar')->assertSee('Brand Strategy');
    }

    public function test_only_active_services_are_public(): void
    {
        $active = Service::factory()->create(['title' => ['en' => 'Visible Service']]);
        $inactive = Service::factory()->inactive()->create(['title' => ['en' => 'Hidden Service']]);

        $this->get('/en')->assertSee('Visible Service')->assertDontSee('Hidden Service');
        $this->get("/en/services/{$active->slug}")->assertOk();
        $this->get("/en/services/{$inactive->slug}")->assertNotFound();
    }

    public function test_only_published_projects_are_public(): void
    {
        $published = Project::factory()->create(['title' => ['en' => 'Live Campaign']]);
        $draft = Project::factory()->unpublished()->create(['title' => ['en' => 'Secret Draft']]);

        $this->get('/en')->assertSee('Live Campaign')->assertDontSee('Secret Draft');
        $this->get('/en/projects')->assertSee('Live Campaign')->assertDontSee('Secret Draft');
        $this->get("/en/projects/{$published->slug}")->assertOk();
        $this->get("/en/projects/{$draft->slug}")->assertNotFound();
    }

    public function test_unknown_locale_and_records_return_404(): void
    {
        $this->get('/de')->assertNotFound();
        $this->get('/en/projects/does-not-exist')->assertNotFound();
        $this->get('/en/services/does-not-exist')->assertNotFound();
    }

    public function test_former_pages_redirect_to_home_sections(): void
    {
        $this->get('/fr/about')->assertRedirect('/fr#about')->assertStatus(301);
        $this->get('/en/contact')->assertRedirect('/en#contact');
    }

    public function test_category_filters_only_list_categories_with_published_projects(): void
    {
        $film = Category::factory()->create(['slug' => 'film', 'name' => ['en' => 'Film']]);
        $branding = Category::factory()->create(['slug' => 'branding', 'name' => ['en' => 'Branding']]);
        $empty = Category::factory()->create(['slug' => 'empty', 'name' => ['en' => 'Nothing Here']]);
        Project::factory()->for($film)->create();
        Project::factory()->for($branding)->create();
        Project::factory()->for($empty)->unpublished()->create();

        $this->get('/en/projects?category=film')
            ->assertOk()
            ->assertSee('data-filter="branding"', false)
            ->assertSeeInOrder(['data-filter="film"', 'is-active'], false)
            ->assertDontSee('Nothing Here');
    }

    public function test_project_images_use_the_media_disk_url(): void
    {
        $project = Project::factory()->create(['thumbnail' => 'projects/cover.jpg']);

        $this->get("/en/projects/{$project->slug}")
            ->assertSee(asset('storage/projects/cover.jpg'), false);
    }

    public function test_contact_details_are_hidden_until_entered(): void
    {
        $this->get('/en')->assertDontSee('mailto:', false)->assertDontSee('tel:', false);

        app(SiteSettings::class)->save([
            'contact_email' => 'studio@example.test',
            'contact_phone' => '+212 600 00 00 00',
            'socials' => ['instagram' => 'https://instagram.com/example'],
        ]);

        $this->get('/en')
            ->assertSee('mailto:studio@example.test', false)
            ->assertSee('tel:+212600000000', false)
            ->assertSee('https://instagram.com/example', false);
    }

    public function test_the_contact_section_embeds_a_google_map_of_the_saved_location(): void
    {
        $this->get('/en')->assertSee('src="https://maps.google.com/maps?q=33.5274554%2C-7.6500933&amp;z=16&amp;hl=en&amp;output=embed"', false);

        app(SiteSettings::class)->save(['map_location' => '"><script>alert(1)</script>']);

        $this->get('/en')
            ->assertSee('https://maps.google.com/maps?q=%22%3E%3Cscript%3E', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_social_profiles_render_as_labelled_icon_links(): void
    {
        app(SiteSettings::class)->save([
            'socials' => ['tiktok' => 'https://www.tiktok.com/@example', 'linkedin' => 'https://www.linkedin.com/company/example/'],
        ]);

        $this->get('/en')
            ->assertSee('href="https://www.tiktok.com/@example"', false)
            ->assertSee('aria-label="TikTok"', false)
            ->assertSee('aria-label="LinkedIn"', false)
            ->assertDontSee('aria-label="Instagram"', false);
    }

    public function test_edited_content_replaces_the_default_copy(): void
    {
        app(SiteSettings::class)->save(['hero_title' => ['en' => 'Bold *ideas* only', 'fr' => 'Idées *audacieuses*']]);

        $this->get('/en')->assertSee('Bold <span class="text-orange">ideas</span> only', false);
        $this->get('/fr')->assertSee('Idées <span class="text-orange">audacieuses</span>', false);
        $this->get('/ar')->assertSee('Bold <span class="text-orange">ideas</span> only', false);
    }

    public function test_the_sitemap_lists_public_pages_only(): void
    {
        $published = Project::factory()->create();
        $draft = Project::factory()->unpublished()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(url("/ar/projects/{$published->slug}"), false)
            ->assertDontSee($draft->slug, false);
    }

    public function test_highlighted_heading_escapes_html(): void
    {
        app(SiteSettings::class)->save(['hero_title' => ['en' => '<script>alert(1)</script> *hi*']]);

        $this->get('/en')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }
}
