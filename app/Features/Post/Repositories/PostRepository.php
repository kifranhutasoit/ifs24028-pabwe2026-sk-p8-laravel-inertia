<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        $query = Post::query()
            ->with('user:id,name,username')
            ->published();

        $this->applySearch($query, $search);

        if ($category !== null && PostCategory::tryFrom($category) !== null) {
            $query->where('category', $category);
        }

        return $query->orderByDesc('published_at')->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with('user:id,name,username')
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function paginateForUser(int $userId, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::query()->where('user_id', $userId);
        $this->applySearch($query, $search);

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function allForUser(int $userId): Collection
    {
        return Post::query()->where('user_id', $userId)->latest('id')->get();
    }

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::query()->with('user:id,name,username');
        $this->applySearch($query, $search);

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function all(): Collection
    {
        return Post::query()->with('user:id,name,username')->latest('id')->get();
    }

    public function statsForUser(int $userId): array
    {
        $base = Post::query()->where('user_id', $userId);

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('is_published', true)->count(),
            'draft' => (clone $base)->where('is_published', false)->count(),
        ];
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function create(array $data): Post
    {
        return Post::query()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->refresh();
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $term = trim((string) $search);

        if ($term === '') {
            return;
        }

        $like = '%'.mb_strtolower($term).'%';

        $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(content) LIKE ?', [$like]);
        });
    }
}
