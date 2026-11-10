<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPostController extends Controller
{
    public function index(Request $request, PostRepositoryInterface $posts): Response
    {
        return Inertia::render('Admin/PostList', [
            'posts' => $posts->paginateAll($request->query('search')),
            'filters' => ['search' => $request->query('search', '')],
        ]);
    }

    public function export(PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->download($posts->all(), 'semua-artikel.xlsx', withAuthor: true);
    }
}
