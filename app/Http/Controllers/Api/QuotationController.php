<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationRequest;
use App\Services\QuotationService;
use InvalidArgumentException;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuotationService $quotationService 
    ) {}

    public function store(QuotationRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $quotation = $this->quotationService->calculate($request->validated());

            return response()->json([
                'total' => $quotation->total,
                'currency_id' => $quotation->currency_id,
                'quotation_id' => $quotation->id,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
