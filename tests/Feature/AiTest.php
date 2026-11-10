<?php

namespace Tests\Feature;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiTest extends TestCase
{
    use RefreshDatabase;

    private function fakeOpenAi(array $posts): void
    {
        config(['openai.api_key' => 'sk-test']);
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['posts' => $posts])]]],
            ]),
        ]);
    }

    public function test_service_parses_openai_response(): void
    {
        $this->fakeOpenAi([
            ['title' => 'AI 1', 'category' => 'Bisnis', 'excerpt' => 'e', 'content' => 'isi'],
            ['title' => 'AI 2', 'category' => 'Ngawur', 'excerpt' => 'e', 'content' => 'isi'],
            ['title' => 'Kosong', 'category' => 'Umum', 'excerpt' => 'e', 'content' => ''],
            'bukan-array',
            ['content' => 'tanpa judul'],
        ]);

        $items = app(OpenAiPostGeneratorService::class)->generate(5, 'bisnis');

        $this->assertCount(3, $items);
        $this->assertSame('Bisnis', $items[0]->category);
        $this->assertSame('Umum', $items[1]->category);
        $this->assertSame('Artikel Tanpa Judul', $items[2]->title);
        $this->assertSame('AI 1', $items[0]->toArray()['title']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer sk-test'));
    }

    public function test_service_handles_missing_topic_and_garbage_content(): void
    {
        config(['openai.api_key' => 'sk-test']);
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => 'bukan json']]]])]);

        $this->assertSame([], app(OpenAiPostGeneratorService::class)->generate(1, null));
        $this->assertSame([], app(OpenAiPostGeneratorService::class)->generate(1, '   '));
    }

    public function test_service_requires_api_key(): void
    {
        config(['openai.api_key' => null]);

        $this->expectException(RuntimeException::class);
        app(OpenAiPostGeneratorService::class)->generate(1);
    }

    public function test_service_throws_when_http_fails(): void
    {
        config(['openai.api_key' => 'sk-test']);
        Http::fake(['*' => Http::response([], 500)]);

        $this->expectException(RuntimeException::class);
        app(OpenAiPostGeneratorService::class)->generate(1);
    }

    public function test_ai_generate_flashes_items(): void
    {
        $this->app->bind(AiClientInterface::class, fn () => new class implements AiClientInterface
        {
            public function generate(int $count, ?string $topic = null): array
            {
                return [GeneratedPostItem::fromArray(['title' => 'T', 'category' => 'Umum', 'excerpt' => 'e', 'content' => 'c'])];
            }
        });

        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-generate', ['count' => 1, 'topic' => 'x'])
            ->assertSessionHas('generated.0.title', 'T');
    }

    public function test_ai_generate_reports_failure_and_validates(): void
    {
        config(['openai.api_key' => null]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 2])->assertSessionHas('error');
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 99])->assertSessionHasErrors('count');
    }

    public function test_ai_save_stores_drafts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts/ai-save', ['posts' => [
            ['title' => 'Satu', 'category' => 'Umum', 'excerpt' => 'e', 'content' => 'c'],
            ['title' => 'Dua', 'category' => 'Opini', 'excerpt' => '', 'content' => 'c'],
        ]])->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['title' => 'Satu', 'is_published' => false, 'user_id' => $user->id]);
        $this->assertDatabaseCount('posts', 2);

        $this->actingAs($user)->post('/dashboard/posts/ai-save', ['posts' => []])->assertSessionHasErrors('posts');
    }
}
