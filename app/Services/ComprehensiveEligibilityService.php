<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Services\Exam\ExamEligibilityService;

class ComprehensiveEligibilityService
{
    public function check(Request $request): array
    {
        return app(ExamEligibilityService::class)->for($request->user());
    }
}
