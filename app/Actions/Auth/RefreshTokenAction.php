<?php

namespace App\Actions\Auth;

class RefreshTokenAction
{
    public function handle(): array
    {
        $token = auth('api')->refresh();

        return ['token' => $token];
    }
}
