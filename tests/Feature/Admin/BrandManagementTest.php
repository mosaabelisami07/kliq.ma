<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_section_is_hidden_without_brands(): void
    {
        $this->get('/en')->assertOk()->assertDontSee('id="brands"', false);
    }

    public function test_active_brands_are_shown_with_their_logo_or_name(): void
    {
        Brand::factory()->create(['name' => 'Kappa', 'logo' => 'brands/kappa.png', 'sort_order' => 1]);
        Brand::factory()->create(['name' => 'inwi', 'sort_order' => 0]);
        Brand::factory()->inactive()->create(['name' => 'Hidden Brand']);

        $this->get('/en')
            ->assertSee('id="brands"', false)
            ->assertSee("Brands we've worked with.")
            ->assertSee(Storage::disk('public')->url('brands/kappa.png'), false)
            ->assertSeeInOrder(['inwi', 'Kappa'])
            ->assertDontSee('Hidden Brand');

        $this->get('/fr')->assertSee('Ils nous ont fait confiance.');
    }

    public function test_an_admin_can_add_update_reorder_and_delete_brands(): void
    {
        $this->actingAs($this->admin)->post('/admin/brands', [
            'name' => 'Stellantis',
            'url' => 'https://www.stellantis.com',
            'logo' => UploadedFile::fake()->image('stellantis.png', 400, 120),
        ])->assertSessionHasNoErrors();

        $brand = Brand::query()->sole();
        Storage::disk('public')->assertExists($brand->logo);
        $this->actingAs($this->admin)->get('/admin/brands')->assertOk()->assertSee('Stellantis');

        $other = Brand::factory()->create(['sort_order' => 5]);
        $this->actingAs($this->admin)->patch("/admin/brands/{$other->id}/move", ['direction' => 'up']);
        $this->assertSame([$other->id, $brand->id], Brand::query()->orderBy('sort_order')->pluck('id')->all());

        $this->actingAs($this->admin)->patch("/admin/brands/{$brand->id}/toggle");
        $this->assertFalse($brand->fresh()->is_active);

        $oldLogo = $brand->logo;
        $this->actingAs($this->admin)->put("/admin/brands/{$brand->id}", ['name' => 'Stellantis Group', 'is_active' => '1', 'remove_logo' => '1']);
        $this->assertSame('Stellantis Group', $brand->fresh()->name);
        $this->assertNull($brand->fresh()->logo);
        Storage::disk('public')->assertMissing($oldLogo);

        $this->actingAs($this->admin)->delete("/admin/brands/{$brand->id}");
        $this->assertModelMissing($brand);
    }

    public function test_brand_input_is_validated(): void
    {
        $this->actingAs($this->admin)->post('/admin/brands', [
            'name' => '',
            'url' => 'javascript:alert(1)',
            'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        ])->assertSessionHasErrors(['name', 'url', 'logo']);
    }

    public function test_guests_cannot_manage_brands(): void
    {
        $this->get('/admin/brands')->assertRedirect('/admin/login');
        $this->post('/admin/brands', ['name' => 'Nope'])->assertRedirect('/admin/login');
        $this->assertDatabaseCount('brands', 0);
    }
}
