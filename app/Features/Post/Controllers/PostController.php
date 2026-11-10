<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostController extends Controller
{
    public function __construct(private readonly PostService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Post/Index', [
            'posts' => $this->service->listForUser($request->user(), $request->query('search')),
            'stats' => $this->service->stats($request->user()),
            'categories' => PostCategory::options(),
            'filters' => ['search' => $request->query('search', '')],
        ]);
    }

    public function show(Post $post): Response
    {
        return Inertia::render('Post/Show', ['post' => $post]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($request->user(), $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->service->update($post, $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->service->delete($post);

        return redirect()->route('dashboard.posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $post = $this->service->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function uploadCover(Request $request, Post $post): RedirectResponse
    {
        $request->validate(['cover' => ['required', 'image', 'max:2048']]);
        $this->service->uploadCover($post, $request->file('cover'));

        return back()->with('success', 'Foto sampul berhasil diunggah.');
    }

    public function removeCover(Post $post): RedirectResponse
    {
        $this->service->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request, PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->download($posts->allForUser($request->user()->id), 'artikel-saya.xlsx');
    }

    public function import(Request $request, PostExcelService $excel): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'extensions:xlsx,xls', 'max:5120']]);

        try {
            $count = $excel->import($request->file('file'), $request->user());
        } catch (Throwable) {
            return back()->with('error', 'Berkas Excel tidak dapat dibaca.');
        }

        return back()->with('success', "{$count} artikel berhasil diimpor.");
    }

    public function aiGenerate(Request $request, AiClientInterface $ai): RedirectResponse
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:5'],
            'topic' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $items = $ai->generate((int) $data['count'], $data['topic'] ?? null);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('generated', array_map(fn ($item) => $item->toArray(), $items));
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'posts' => ['required', 'array', 'min:1', 'max:5'],
            'posts.*.title' => ['required', 'string', 'max:255'],
            'posts.*.category' => ['required', Rule::in(PostCategory::values())],
            'posts.*.excerpt' => ['nullable', 'string', 'max:500'],
            'posts.*.content' => ['required', 'string'],
        ]);

        foreach ($data['posts'] as $item) {
            $this->service->create($request->user(), $item + ['is_published' => false]);
        }

        return back()->with('success', count($data['posts']).' artikel AI disimpan sebagai draf.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(PostCategory::values())],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}