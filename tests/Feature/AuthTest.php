<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render_for_guest(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_member_can_login_and_is_redirected_to_dashboard(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia'])
            ->assertRedirect('/dashboard/posts');

        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        User::factory()->admin()->create(['username' => 'admin', 'password' => 'admin']);

        $this->post('/login', ['username' => 'admin', 'password' => 'admin'])
            ->assertRedirect('/admin/users');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        $this->from('/login')
            ->post('/login', ['username' => 'budi', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_login_requires_fields(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
    }

    public function test_user_can_register_as_member(): void
    {
        $this->post('/register', [
            'name' => 'Siti',
            'username' => 'siti_01',
            'email' => 'siti@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertRedirect('/dashboard/posts');

        $this->assertDatabaseHas('users', ['username' => 'siti_01', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'dipakai']);

        $this->post('/register', [
            'name' => '',
            'username' => 'dipakai',
            'password' => '123',
        ])->assertSessionHasErrors(['name', 'username', 'password']);
    }

    public function test_logout_clears_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_open_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect();
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
    }
}
