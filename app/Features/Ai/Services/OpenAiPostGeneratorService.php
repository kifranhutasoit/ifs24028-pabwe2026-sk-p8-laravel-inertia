<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generate(int $count, ?string $topic = null): array
    {
        $apiKey = config('openai.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY belum diatur di file .env.');
        }

        $categories = implode(', ', PostCategory::values());
        $subject = $topic !== null && trim($topic) !== '' ? "bertema \"{$topic}\"" : 'dengan topik bebas yang menarik';

        $response = Http::withToken($apiKey)
            ->baseUrl(config('openai.base_url'))
            ->timeout(config('openai.timeout'))
            ->post('/chat/completions', [
                'model' => config('openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu penulis blog berbahasa Indonesia. Balas HANYA dengan JSON: '
                            .'{"posts":[{"title":"","category":"","excerpt":"","content":""}]}. '
                            ."Nilai category harus salah satu dari: {$categories}. "
                            .'Field content berformat Markdown (minimal 3 paragraf dan 1 subjudul).',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Buatkan {$count} artikel blog {$subject}.",
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal menghubungi OpenAI (HTTP '.$response->status().').');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $items = is_array($decoded) ? ($decoded['posts'] ?? []) : [];

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(fn (array $item) => GeneratedPostItem::fromArray($item))
            ->filter(fn (GeneratedPostItem $item) => $item->content !== '')
            ->take($count)
            ->values()
            ->all();
    }
}
