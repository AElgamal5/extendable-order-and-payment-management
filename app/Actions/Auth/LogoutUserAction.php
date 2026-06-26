<?php

namespace App\Actions\Auth;

class LogoutUserAction
{
    public function handle(): void
    {
        auth('api')->logout();
    }
}
