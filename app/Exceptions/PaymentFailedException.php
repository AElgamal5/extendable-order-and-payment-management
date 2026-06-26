<?php

namespace App\Exceptions;

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
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
