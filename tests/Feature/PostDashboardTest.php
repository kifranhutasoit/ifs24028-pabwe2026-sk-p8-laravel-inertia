<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Judul Baru',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkas',
            'content' => '## Isi',
            'is_published' => true,
        ], $override);
    }

    public function test_index_lists_own_posts_with_stats_and_search(): void
    {
        Post::factory()->count(2)->for($this->user)->create(['title' => 'Alpha']);
        Post::factory()->draft()->for($this->user)->create(['title' => 'Beta']);
        Post::factory()->create(['title' => 'Milik Orang Lain']);

        $this->actingAs($this->user)->get('/dashboard/posts')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Post/Index')
                ->has('posts.data', 3)
                ->where('stats', ['total' => 3, 'published' => 2, 'draft' => 1]));

        $this->actingAs($this->user)->get('/dashboard/posts?search=beta')
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    public function test_store_creates_post_with_unique_slug_and_cover(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['cover' => UploadedFile::fake()->image('c.jpg')]))
            ->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['is_published' => false]));

        $this->assertDatabaseHas('posts', ['slug' => 'judul-baru', 'is_published' => true]);
        $this->assertDatabaseHas('posts', ['slug' => 'judul-baru-2', 'is_published' => false, 'published_at' => null]);

        $first = Post::where('slug', 'judul-baru')->first();
        Storage::disk('public')->assertExists($first->cover_image_path);
        $this->assertNotNull($first->cover_url);
    }

    public function test_slug_falls_back_when_title_has_no_letters(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['title' => '!!!']));

        $this->assertDatabaseHas('posts', ['slug' => 'artikel']);
    }

    public function test_store_validates_input(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', ['category' => 'Salah'])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_show_update_and_destroy_own_post(): void
    {
        $post = Post::factory()->for($this->user)->create(['title' => 'Lama']);

        $this->actingAs($this->user)->get('/dashboard/posts/'.$post->id)
            ->assertInertia(fn (Assert $page) => $page->component('Post/Show'));

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => 'Lama']))
            ->assertSessionHas('success');
        $this->assertSame('lama', $post->fresh()->slug);

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => 'Baru Lagi', 'is_published' => false]));
        $post->refresh();
        $this->assertSame('baru-lagi', $post->slug);
        $this->assertNull($post->published_at);

        $this->actingAs($this->user)->delete('/dashboard/posts/'.$post->id)
            ->assertRedirect('/dashboard/posts');
        $this->assertModelMissing($post);
    }

    public function test_update_keeps_existing_published_at_and_replaces_cover(): void
    {
        $post = Post::factory()->for($this->user)->create(['published_at' => now()->subDay()]);
        $stamp = $post->published_at;

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => $post->title, 'cover' => UploadedFile::fake()->image('a.jpg')]));
        $first = $post->fresh()->cover_image_path;
        $this->assertTrue($post->fresh()->published_at->equalTo($stamp));

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => $post->title, 'cover' => UploadedFile::fake()->image('b.jpg')]));
        Storage::disk('public')->assertMissing($first);
    }

    public function test_destroy_removes_cover_file(): void
    {
        $post = Post::factory()->for($this->user)->create();
        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')]);
        $path = $post->fresh()->cover_image_path;

        $this->actingAs($this->user)->delete('/dashboard/posts/'.$post->id);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_toggle_publish(): void
    {
        $post = Post::factory()->draft()->for($this->user)->create();

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success');
        $this->assertTrue($post->fresh()->is_published);
        $this->assertNotNull($post->fresh()->published_at);

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish");
        $this->assertFalse($post->fresh()->is_published);
    }

    public function test_upload_and_remove_cover(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')]);
        $first = $post->fresh()->cover_image_path;
        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('b.jpg')]);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", [])->assertSessionHasErrors('cover');

        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}/cover");
        $this->assertNull($post->fresh()->cover_image_path);
        $this->assertNull($post->fresh()->cover_url);
    }

    public function test_member_cannot_touch_other_users_posts(): void
    {
        $other = Post::factory()->create();

        $this->actingAs($this->user)->get('/dashboard/posts/'.$other->id)->assertForbidden();
        $this->actingAs($this->user)->put('/dashboard/posts/'.$other->id, $this->payload())->assertForbidden();
        $this->actingAs($this->user)->delete('/dashboard/posts/'.$other->id)->assertForbidden();
    }

    public function test_admin_cannot_use_member_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/posts')->assertForbidden();
    }
}
