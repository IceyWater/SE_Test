<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Exam\ExamEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComprehensiveExamEligibilityController extends Controller
{
    public function checkEligibility(Request $request): JsonResponse
    {
        return response()->json(app(ExamEligibilityService::class)->for($request->user()));
    }

    public function manualDataScraping(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Manual request-time scraping is disabled. Academic data must be refreshed through the verified snapshot sync workflow.',
        ], 410);
    }
}
