<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $term = trim((string) $search);

        return User::query()
            ->withCount('posts')
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.mb_strtolower($term).'%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(username) LIKE ?', [$like]);
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
