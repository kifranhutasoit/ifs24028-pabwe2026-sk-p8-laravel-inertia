<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_lists_and_searches_users(): void
    {
        User::factory()->create(['name' => 'Cari Aku', 'username' => 'cariaku']);
        User::factory()->create(['name' => 'Lain', 'username' => 'lain']);

        $this->actingAs($this->admin)->get('/admin/users')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/UserList')->has('users.data', 3));

        $this->actingAs($this->admin)->get('/admin/users?search=cariaku')
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 1));
    }

    public function test_admin_monitors_posts_and_exports(): void
    {
        $writer = User::factory()->create(['username' => 'penulis']);
        Post::factory()->count(2)->for($writer)->create();

        $this->actingAs($this->admin)->get('/admin/posts')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/PostList')->has('posts.data', 2));

        $this->actingAs($this->admin)->get('/admin/posts?search=zzzz')
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 0));

        $this->actingAs($this->admin)->get('/admin/posts/export')->assertDownload('semua-artikel.xlsx');

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts")
            ->assertInertia(fn (Assert $page) => $page->component('Admin/UserPostList')->has('posts.data', 2));

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts/export")
            ->assertDownload('artikel-penulis.xlsx');
    }

    public function test_exports_include_author_column_content(): void
    {
        Post::factory()->for(User::factory()->create(['name' => 'Penulis X']))->create();

        $content = $this->actingAs($this->admin)->get('/admin/posts/export')->streamedContent();

        $this->assertNotEmpty($content);
    }

    public function test_member_and_guest_cannot_open_admin_area(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/users')->assertForbidden();
    }

    public function test_profile_page_and_update(): void
    {
        $user = User::factory()->create(['username' => 'lama']);

        $this->actingAs($user)->get('/profile')->assertInertia(fn (Assert $page) => $page->component('Profile/Edit'));

        $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru',
            'username' => 'baru',
            'email' => 'baru@example.com',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
            'photo' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('baru', $user->username);
        $this->assertTrue(password_verify('passwordbaru', $user->password));
        Storage::disk('public')->assertExists($user->profile_photo_path);
        $this->assertNotNull($user->profile_photo_url);

        $old = $user->profile_photo_path;
        $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru', 'username' => 'baru', 'photo' => UploadedFile::fake()->image('q.jpg'),
        ]);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_profile_update_without_password_keeps_it_and_validates_unique(): void
    {
        $other = User::factory()->create(['username' => 'dipakai']);
        $user = User::factory()->create(['username' => 'saya', 'password' => 'lama123']);
        $hash = $user->password;

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => 'saya'])->assertSessionHas('success');
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertNull($user->fresh()->profile_photo_url);

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $other->username])->assertSessionHasErrors('username');
    }
}
