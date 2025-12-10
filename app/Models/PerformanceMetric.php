<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'strategy',
        'performance_score',
        'accessibility_score',
        'best_practices_score',
        'seo_score',
        'first_contentful_paint',
        'largest_contentful_paint',
        'total_blocking_time',
        'cumulative_layout_shift',
        'speed_index',
        'time_to_interactive',
        'raw_data',
    ];

    protected $casts = [
        'performance_score' => 'integer',
        'accessibility_score' => 'integer',
        'best_practices_score' => 'integer',
        'seo_score' => 'integer',
        'first_contentful_paint' => 'float',
        'largest_contentful_paint' => 'float',
        'total_blocking_time' => 'float',
        'cumulative_layout_shift' => 'float',
        'speed_index' => 'float',
        'time_to_interactive' => 'float',
        'raw_data' => 'array',
    ];

    /**
     * Get the average score across all categories
     */
    public function getAverageScoreAttribute(): float
    {
        $scores = array_filter([
            $this->performance_score,
            $this->accessibility_score,
            $this->best_practices_score,
            $this->seo_score,
        ]);

        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    /**
     * Get performance grade (A-F)
     */
    public function getGradeAttribute(): string
    {
        $score = $this->performance_score ?? 0;

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    /**
     * Scope for a specific URL
     */
    public function scopeForUrl($query, string $url)
    {
        return $query->where('url', $url);
    }

    /**
     * Scope for a specific strategy
     */
    public function scopeStrategy($query, string $strategy)
    {
        return $query->where('strategy', $strategy);
    }

    /**
     * Get latest metrics for each URL
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get metrics from the last N days
     */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get trend data for charts
     */
    public static function getTrendData(string $url, int $days = 30): array
    {
        return static::forUrl($url)
            ->lastDays($days)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($metric) => [
                'date' => $metric->created_at->format('Y-m-d H:i'),
                'performance' => $metric->performance_score,
                'accessibility' => $metric->accessibility_score,
                'best_practices' => $metric->best_practices_score,
                'seo' => $metric->seo_score,
                'lcp' => round($metric->largest_contentful_paint / 1000, 2), // Convert to seconds
                'fcp' => round($metric->first_contentful_paint / 1000, 2),
                'cls' => $metric->cumulative_layout_shift,
            ])
            ->toArray();
    }
}
