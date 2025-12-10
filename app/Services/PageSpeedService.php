<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PerformanceMetric;

class PageSpeedService
{
    private string $apiKey;
    private string $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public function __construct()
    {
        $this->apiKey = config('services.google.pagespeed_api_key', '');
    }

    /**
     * Run PageSpeed analysis on a URL
     */
    public function analyze(string $url, string $strategy = 'mobile'): ?array
    {
        try {
            $params = [
                'url' => $url,
                'strategy' => $strategy, // 'mobile' or 'desktop'
                'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
            ];

            if ($this->apiKey) {
                $params['key'] = $this->apiKey;
            }

            $response = Http::timeout(120)->get($this->apiUrl, $params);

            if (!$response->successful()) {
                Log::error('PageSpeed API error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $this->parseResponse($response->json(), $url, $strategy);
        } catch (\Exception $e) {
            Log::error('PageSpeed analysis failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Parse the PageSpeed API response
     */
    private function parseResponse(array $data, string $url, string $strategy): array
    {
        $lighthouseResult = $data['lighthouseResult'] ?? [];
        $categories = $lighthouseResult['categories'] ?? [];
        $audits = $lighthouseResult['audits'] ?? [];

        return [
            'url' => $url,
            'strategy' => $strategy,
            'scores' => [
                'performance' => $this->getScore($categories, 'performance'),
                'accessibility' => $this->getScore($categories, 'accessibility'),
                'best_practices' => $this->getScore($categories, 'best-practices'),
                'seo' => $this->getScore($categories, 'seo'),
            ],
            'metrics' => [
                'first_contentful_paint' => $this->getMetricValue($audits, 'first-contentful-paint'),
                'largest_contentful_paint' => $this->getMetricValue($audits, 'largest-contentful-paint'),
                'total_blocking_time' => $this->getMetricValue($audits, 'total-blocking-time'),
                'cumulative_layout_shift' => $this->getMetricValue($audits, 'cumulative-layout-shift'),
                'speed_index' => $this->getMetricValue($audits, 'speed-index'),
                'time_to_interactive' => $this->getMetricValue($audits, 'interactive'),
            ],
            'raw_data' => $data,
        ];
    }

    private function getScore(array $categories, string $key): ?int
    {
        return isset($categories[$key]['score'])
            ? (int) round($categories[$key]['score'] * 100)
            : null;
    }

    private function getMetricValue(array $audits, string $key): ?float
    {
        return $audits[$key]['numericValue'] ?? null;
    }

    /**
     * Run analysis and save to database
     */
    public function analyzeAndSave(string $url, string $strategy = 'mobile'): ?PerformanceMetric
    {
        $result = $this->analyze($url, $strategy);

        if (!$result) {
            return null;
        }

        return PerformanceMetric::create([
            'url' => $result['url'],
            'strategy' => $result['strategy'],
            'performance_score' => $result['scores']['performance'],
            'accessibility_score' => $result['scores']['accessibility'],
            'best_practices_score' => $result['scores']['best_practices'],
            'seo_score' => $result['scores']['seo'],
            'first_contentful_paint' => $result['metrics']['first_contentful_paint'],
            'largest_contentful_paint' => $result['metrics']['largest_contentful_paint'],
            'total_blocking_time' => $result['metrics']['total_blocking_time'],
            'cumulative_layout_shift' => $result['metrics']['cumulative_layout_shift'],
            'speed_index' => $result['metrics']['speed_index'],
            'time_to_interactive' => $result['metrics']['time_to_interactive'],
            'raw_data' => $result['raw_data'],
        ]);
    }

    /**
     * Analyze multiple URLs
     */
    public function analyzeMultiple(array $urls, string $strategy = 'mobile'): array
    {
        $results = [];

        foreach ($urls as $url) {
            $results[] = $this->analyzeAndSave($url, $strategy);
        }

        return array_filter($results);
    }
}
