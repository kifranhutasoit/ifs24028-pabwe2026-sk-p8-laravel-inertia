<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'admin',
            'role' => User::ROLE_ADMIN,
        ]);

        $member = User::query()->updateOrCreate(['username' => 'member'], [
            'name' => 'Penulis Contoh',
            'email' => 'member@example.com',
            'password' => 'member',
            'role' => User::ROLE_MEMBER,
        ]);

        if ($member->posts()->count() === 0) {
            Post::factory()->count(6)->for($member)->create();
            Post::factory()->count(2)->draft()->for($member)->create();
        }
    }
}
