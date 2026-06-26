<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentFailedException extends Exception
{
    public function __construct(string $message = 'Payment processing failed.')
    {
        parent::__construct($message, 422);
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->getCode());
    }
}
