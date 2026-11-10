<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function index(Request $request, PostService $service): Response
    {
        return Inertia::render('Public/PostIndex', [
            'posts' => $service->publicIndex($request->query('search'), $request->query('category')),
            'categories' => PostCategory::options(),
            'filters' => [
                'search' => $request->query('search', ''),
                'category' => $request->query('category', ''),
            ],
        ]);
    }

    public function show(string $slug, PostService $service): Response
    {
        $post = $service->findPublished($slug);

        abort_if($post === null, 404);

        return Inertia::render('Public/PostShow', ['post' => $post]);
    }
}
