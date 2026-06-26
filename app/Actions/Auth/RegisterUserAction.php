<?php

namespace App\Actions\Auth;

use App\Models\User;

class RegisterUserAction
{
    public function handle(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = auth('api')->login($user);

        return ['user' => $user, 'token' => $token];
    }
}
