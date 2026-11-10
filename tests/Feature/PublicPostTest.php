<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_only_published_posts(): void
    {
        Post::factory()->create(['title' => 'Terbit']);
        Post::factory()->draft()->create(['title' => 'Masih Draf']);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/PostIndex')
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Terbit'));
    }

    public function test_articles_route_supports_search_and_category_filter(): void
    {
        Post::factory()->create(['title' => 'Belajar Laravel', 'category' => 'Teknologi']);
        Post::factory()->create(['title' => 'Resep Sehat', 'category' => 'Gaya Hidup']);

        $this->get('/articles?'.http_build_query(['search' => 'laravel']))
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));

        $this->get('/articles?'.http_build_query(['category' => 'Gaya Hidup']))
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'Resep Sehat'));

        $this->get('/articles?'.http_build_query(['category' => 'TidakAda']))
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 2));

        $this->get('/articles?'.http_build_query(['search' => '   ']))
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 2));
    }

    public function test_guest_can_read_published_article_by_slug(): void
    {
        $post = Post::factory()->create(['slug' => 'artikel-satu']);

        $this->get('/articles/'.$post->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/PostShow')->where('post.id', $post->id));
    }

    public function test_draft_or_missing_article_returns_404(): void
    {
        $draft = Post::factory()->draft()->create(['slug' => 'draf']);

        $this->get('/articles/'.$draft->slug)->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }
}