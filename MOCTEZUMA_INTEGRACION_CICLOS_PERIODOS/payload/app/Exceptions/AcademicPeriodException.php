<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class AcademicPeriodException extends RuntimeException
{
    public function render(Request $request): Response|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
            ], 422);
        }

        return response($this->getMessage(), 422);
    }
}
