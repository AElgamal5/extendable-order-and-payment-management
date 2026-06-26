<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\ListPaymentsAction;
use App\Actions\Payments\ProcessPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request, ListPaymentsAction $action): JsonResponse
    {
        $payments = $action->handle($request->query('order_id'));

        return ApiResponse::success(
            data: PaymentResource::collection($payments),
            message: 'Payments retrieved successfully.',
            meta: [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        );
    }

    public function store(ProcessPaymentRequest $request, ProcessPaymentAction $action): JsonResponse
    {
        $payment = $action->handle(
            $request->input('order_id'),
            $request->input('method'),
        );

        return ApiResponse::created([
            'payment' => new PaymentResource($payment),
        ], 'Payment processed successfully.');
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load('order');

        return ApiResponse::success([
            'payment' => new PaymentResource($payment),
        ]);
    }
}
