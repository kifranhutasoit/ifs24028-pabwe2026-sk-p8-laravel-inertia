<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function publicIndex(?string $search, ?string $category): LengthAwarePaginator
    {
        return $this->posts->paginatePublished($search, $category);
    }

    public function findPublished(string $slug): ?Post
    {
        return $this->posts->findPublishedBySlug($slug);
    }

    public function listForUser(User $user, ?string $search): LengthAwarePaginator
    {
        return $this->posts->paginateForUser($user->id, $search);
    }

    public function stats(User $user): array
    {
        return $this->posts->statsForUser($user->id);
    }

    public function create(User $user, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        return $this->posts->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'category' => $data['category'] ?? 'Umum',
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'cover_image_path' => $cover?->store('covers', 'public'),
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);
    }

    public function update(Post $post, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        $attributes = [
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title'], $post->id),
            'category' => $data['category'] ?? 'Umum',
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'is_published' => $published,
            'published_at' => $published ? ($post->published_at ?? now()) : null,
        ];

        if ($cover !== null) {
            $this->deleteCoverFile($post);
            $attributes['cover_image_path'] = $cover->store('covers', 'public');
        }

        return $this->posts->update($post, $attributes);
    }

    public function delete(Post $post): void
    {
        $this->deleteCoverFile($post);
        $this->posts->delete($post);
    }

    public function togglePublish(Post $post): Post
    {
        $published = ! $post->is_published;

        return $this->posts->update($post, [
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);
    }

    public function uploadCover(Post $post, UploadedFile $cover): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, [
            'cover_image_path' => $cover->store('covers', 'public'),
        ]);
    }

    public function removeCover(Post $post): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    public function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base === '' ? 'artikel' : Str::limit($base, 200, '');
        $slug = $base;
        $counter = 2;

        while ($this->posts->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function deleteCoverFile(Post $post): void
    {
        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }
    }
}