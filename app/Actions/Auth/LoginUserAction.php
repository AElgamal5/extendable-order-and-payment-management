<?php

namespace App\Actions\Auth;

use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function handle(array $credentials): array
    {
        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return ['token' => $token];
    }
}
