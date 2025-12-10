<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceMetric;
use App\Services\PageSpeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function __construct(
        private PageSpeedService $pageSpeedService
    ) {}

    /**
     * Get latest performance metrics
     */
    public function index(Request $request): JsonResponse
    {
        $query = PerformanceMetric::query();

        if ($request->has('url')) {
            $query->forUrl($request->url);
        }

        if ($request->has('strategy')) {
            $query->strategy($request->strategy);
        }

        $metrics = $query->latest()
            ->take($request->get('limit', 50))
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'strategy' => $m->strategy,
                'scores' => [
                    'performance' => $m->performance_score,
                    'accessibility' => $m->accessibility_score,
                    'best_practices' => $m->best_practices_score,
                    'seo' => $m->seo_score,
                    'average' => round($m->average_score, 1),
                ],
                'grade' => $m->grade,
                'core_web_vitals' => [
                    'lcp' => $m->largest_contentful_paint ? round($m->largest_contentful_paint / 1000, 2) . 's' : null,
                    'fcp' => $m->first_contentful_paint ? round($m->first_contentful_paint / 1000, 2) . 's' : null,
                    'cls' => $m->cumulative_layout_shift,
                    'tbt' => $m->total_blocking_time ? round($m->total_blocking_time) . 'ms' : null,
                ],
                'created_at' => $m->created_at->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get performance summary/dashboard data
     */
    public function summary(): JsonResponse
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        // Get latest metric for each main page
        $pages = [
            'home' => $frontendUrl,
            'productos' => $frontendUrl . '/productos',
            'categorias' => $frontendUrl . '/categorias',
            'contacto' => $frontendUrl . '/contacto',
        ];

        $summary = [];

        foreach ($pages as $name => $url) {
            $mobile = PerformanceMetric::forUrl($url)->strategy('mobile')->latest()->first();
            $desktop = PerformanceMetric::forUrl($url)->strategy('desktop')->latest()->first();

            $summary[$name] = [
                'url' => $url,
                'mobile' => $mobile ? [
                    'performance' => $mobile->performance_score,
                    'grade' => $mobile->grade,
                    'lcp' => $mobile->largest_contentful_paint ? round($mobile->largest_contentful_paint / 1000, 2) : null,
                    'updated_at' => $mobile->created_at->diffForHumans(),
                ] : null,
                'desktop' => $desktop ? [
                    'performance' => $desktop->performance_score,
                    'grade' => $desktop->grade,
                    'lcp' => $desktop->largest_contentful_paint ? round($desktop->largest_contentful_paint / 1000, 2) : null,
                    'updated_at' => $desktop->created_at->diffForHumans(),
                ] : null,
            ];
        }

        // Calculate averages
        $allMobile = PerformanceMetric::strategy('mobile')
            ->whereIn('url', array_values($pages))
            ->latest()
            ->take(count($pages))
            ->get();

        $avgPerformance = $allMobile->avg('performance_score');
        $avgAccessibility = $allMobile->avg('accessibility_score');
        $avgSeo = $allMobile->avg('seo_score');

        return response()->json([
            'success' => true,
            'data' => [
                'pages' => $summary,
                'averages' => [
                    'performance' => round($avgPerformance ?? 0),
                    'accessibility' => round($avgAccessibility ?? 0),
                    'seo' => round($avgSeo ?? 0),
                ],
                'last_audit' => PerformanceMetric::latest()->first()?->created_at?->diffForHumans(),
            ],
        ]);
    }

    /**
     * Get trend data for a specific URL
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'days' => 'integer|min:1|max:90',
        ]);

        $trends = PerformanceMetric::getTrendData(
            $request->url,
            $request->get('days', 30)
        );

        return response()->json([
            'success' => true,
            'data' => $trends,
        ]);
    }

    /**
     * Run a new audit (admin only)
     */
    public function runAudit(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'strategy' => 'in:mobile,desktop',
        ]);

        $metric = $this->pageSpeedService->analyzeAndSave(
            $request->url,
            $request->get('strategy', 'mobile')
        );

        if (!$metric) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run audit. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Audit completed successfully',
            'data' => [
                'id' => $metric->id,
                'url' => $metric->url,
                'strategy' => $metric->strategy,
                'scores' => [
                    'performance' => $metric->performance_score,
                    'accessibility' => $metric->accessibility_score,
                    'best_practices' => $metric->best_practices_score,
                    'seo' => $metric->seo_score,
                ],
                'grade' => $metric->grade,
            ],
        ]);
    }
}
