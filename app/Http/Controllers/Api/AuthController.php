<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Actions\Auth\RefreshTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());

        return ApiResponse::created([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'User registered successfully.');
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());

        return ApiResponse::success([
            'token' => $result['token'],
        ], 'Login successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function logout(LogoutUserAction $action): JsonResponse
    {
        $action->handle();

        return ApiResponse::ok('Logged out successfully.');
    }

    public function refresh(RefreshTokenAction $action): JsonResponse
    {
        $result = $action->handle();

        return ApiResponse::success([
            'token' => $result['token'],
        ], 'Token refreshed successfully.');
    }
}
