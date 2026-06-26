<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderNotConfirmedException extends Exception
{
    public function __construct(string $message = 'Payments can only be processed for confirmed orders.')
    {
        parent::__construct($message, 409);
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->getCode());
    }
}
