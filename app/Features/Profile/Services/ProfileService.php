<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function update(User $user, array $data, ?UploadedFile $photo = null): User
    {
        $attributes = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        if ($photo !== null) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $attributes['profile_photo_path'] = $photo->store('profiles', 'public');
        }

        $user->update($attributes);

        return $user->refresh();
    }
}
