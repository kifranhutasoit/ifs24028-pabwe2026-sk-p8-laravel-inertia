<?php

namespace App\Features\Auth\Actions;

use App\Models\User;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => User::ROLE_MEMBER,
        ]);
    }
}
