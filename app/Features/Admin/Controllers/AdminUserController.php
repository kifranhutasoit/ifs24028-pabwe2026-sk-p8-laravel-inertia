<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request, AdminUserService $service): Response
    {
        return Inertia::render('Admin/UserList', [
            'users' => $service->paginate($request->query('search')),
            'filters' => ['search' => $request->query('search', '')],
        ]);
    }

    public function posts(User $user, PostRepositoryInterface $posts): Response
    {
        return Inertia::render('Admin/UserPostList', [
            'owner' => $user->only(['id', 'name', 'username']),
            'posts' => $posts->paginateForUser($user->id, null),
        ]);
    }

    public function exportPosts(User $user, PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->download($posts->allForUser($user->id), "artikel-{$user->username}.xlsx");
    }
}
