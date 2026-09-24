<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Ari ang mga report (sales, summary, etc.)
class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    // Sales summary karong adlaw
    public function dailySales(Request $request): JsonResponse
    {
        $date = $request->query('date', today()->toDateString());
        return response()->json($this->reportService->getDailySales($date));
    }

    // Sales summary sa usa ka bulan
    public function monthlySales(Request $request): JsonResponse
    {
        $month = $request->query('month', now()->format('Y-m'));
        return response()->json($this->reportService->getMonthlySales($month));
    }

    // Top-selling nga mga produkto, optional date filter
    public function topProducts(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $date  = $request->query('date');
        return response()->json($this->reportService->getTopProducts($limit, $date));
    }
}
