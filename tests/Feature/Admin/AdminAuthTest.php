<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function protectedPagesProvider(): array
    {
        return [
            'dashboard' => ['/admin'],
            'inquiries' => ['/admin/inquiries'],
            'export' => ['/admin/inquiries/export'],
            'projects' => ['/admin/projects'],
            'services' => ['/admin/services'],
            'categories' => ['/admin/categories'],
            'content' => ['/admin/content'],
            'settings' => ['/admin/settings'],
            'profile' => ['/admin/profile'],
        ];
    }

    #[DataProvider('protectedPagesProvider')]
    public function test_guests_are_sent_to_the_login_page(string $path): void
    {
        $this->get($path)->assertRedirect('/admin/login');
    }

    #[DataProvider('protectedPagesProvider')]
    public function test_users_who_are_not_admins_are_forbidden(string $path): void
    {
        $this->actingAs(User::factory()->create())->get($path)->assertForbidden();
    }

    #[DataProvider('protectedPagesProvider')]
    public function test_admins_can_open_every_page(string $path): void
    {
        $this->actingAs(User::factory()->admin()->create())->get($path)->assertOk();
    }

    public function test_admin_pages_are_not_cached_or_indexed(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeaderContains('Cache-Control', 'no-store');
    }

    public function test_an_admin_can_sign_in_and_out(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'boss@kliq.test']);

        $this->get('/admin/login')->assertOk()->assertDontSee('access_code');

        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_wrong_credentials_and_non_admins_get_the_same_error(): void
    {
        User::factory()->admin()->create(['email' => 'boss@kliq.test']);
        User::factory()->create(['email' => 'member@kliq.test']);

        foreach ([['boss@kliq.test', 'wrong'], ['member@kliq.test', 'password'], ['nobody@kliq.test', 'password']] as [$email, $password]) {
            $this->from('/admin/login')
                ->post('/admin/login', ['email' => $email, 'password' => $password])
                ->assertRedirect('/admin/login')
                ->assertSessionHasErrors(['email' => __('auth.failed')]);
        }

        $this->assertGuest();
    }

    public function test_login_is_locked_after_repeated_failures(): void
    {
        User::factory()->admin()->create(['email' => 'boss@kliq.test']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_the_access_code_is_required_when_configured(): void
    {
        config(['kliq.admin.access_code' => 'orange-door-42']);
        User::factory()->admin()->create(['email' => 'boss@kliq.test']);

        $this->get('/admin/login')->assertSee('access_code', false)->assertDontSee('orange-door-42');

        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password'])
            ->assertSessionHasErrors('access_code');
        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password', 'access_code' => 'guess'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password', 'access_code' => 'orange-door-42'])
            ->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_the_access_code_can_be_stored_as_a_hash(): void
    {
        config(['kliq.admin.access_code' => Hash::make('orange-door-42')]);
        User::factory()->admin()->create(['email' => 'boss@kliq.test']);

        $this->post('/admin/login', ['email' => 'boss@kliq.test', 'password' => 'password', 'access_code' => 'orange-door-42'])
            ->assertRedirect('/admin');
    }

    public function test_signed_in_admins_skip_the_login_page(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin/login')->assertRedirect('/admin');
    }

    public function test_the_admin_flag_cannot_be_mass_assigned(): void
    {
        $user = User::query()->create(['name' => 'Eve', 'email' => 'eve@example.test', 'password' => 'secret-password-1', 'is_admin' => true]);

        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_the_kliq_admin_command_creates_an_administrator(): void
    {
        $this->artisan('kliq:admin', ['--email' => 'owner@kliq.test', '--name' => 'Owner'])
            ->expectsQuestion('Password', 'a-strong-passw0rd')
            ->expectsQuestion('Confirm password', 'a-strong-passw0rd')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'owner@kliq.test')->sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('a-strong-passw0rd', $admin->password));
    }

    public function test_profile_changes_require_the_current_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put('/admin/profile', ['name' => 'New Name', 'email' => $admin->email, 'current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($admin)
            ->put('/admin/profile', ['name' => 'New Name', 'email' => $admin->email, 'current_password' => 'password', 'password' => 'brand-new-pass-1', 'password_confirmation' => 'brand-new-pass-1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('New Name', $admin->fresh()->name);
        $this->assertTrue(Hash::check('brand-new-pass-1', $admin->fresh()->password));
    }
}
