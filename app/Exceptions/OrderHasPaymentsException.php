<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderHasPaymentsException extends Exception
{
    public function __construct(string $message = 'Cannot delete order with associated payments.')
    {
        parent::__construct($message, 409);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
