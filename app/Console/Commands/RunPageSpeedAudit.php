<?php

namespace App\Console\Commands;

use App\Services\PageSpeedService;
use Illuminate\Console\Command;

class RunPageSpeedAudit extends Command
{
    protected $signature = 'pagespeed:audit
                            {--url= : Specific URL to audit (default: all configured URLs)}
                            {--strategy=mobile : Strategy to use (mobile/desktop)}
                            {--both : Run both mobile and desktop audits}';

    protected $description = 'Run PageSpeed Insights audit on frontend URLs';

    public function handle(PageSpeedService $pageSpeed): int
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        // URLs to audit
        $urls = $this->option('url')
            ? [$this->option('url')]
            : [
                $frontendUrl,
                $frontendUrl . '/productos',
                $frontendUrl . '/categorias',
                $frontendUrl . '/contacto',
            ];

        $strategies = $this->option('both')
            ? ['mobile', 'desktop']
            : [$this->option('strategy')];

        $this->info('Starting PageSpeed audits...');
        $this->newLine();

        $results = [];

        foreach ($urls as $url) {
            foreach ($strategies as $strategy) {
                $this->line("Auditing: {$url} ({$strategy})");

                $metric = $pageSpeed->analyzeAndSave($url, $strategy);

                if ($metric) {
                    $results[] = [
                        'url' => $url,
                        'strategy' => $strategy,
                        'performance' => $metric->performance_score,
                        'accessibility' => $metric->accessibility_score,
                        'best_practices' => $metric->best_practices_score,
                        'seo' => $metric->seo_score,
                        'grade' => $metric->grade,
                    ];

                    $this->info("  Performance: {$metric->performance_score}/100 (Grade: {$metric->grade})");
                    $this->info("  Accessibility: {$metric->accessibility_score}/100");
                    $this->info("  Best Practices: {$metric->best_practices_score}/100");
                    $this->info("  SEO: {$metric->seo_score}/100");
                } else {
                    $this->error("  Failed to analyze {$url}");
                }

                $this->newLine();
            }
        }

        if (count($results) > 0) {
            $this->table(
                ['URL', 'Strategy', 'Performance', 'A11y', 'Best Practices', 'SEO', 'Grade'],
                collect($results)->map(fn ($r) => [
                    strlen($r['url']) > 40 ? '...' . substr($r['url'], -37) : $r['url'],
                    $r['strategy'],
                    $r['performance'],
                    $r['accessibility'],
                    $r['best_practices'],
                    $r['seo'],
                    $r['grade'],
                ])->toArray()
            );
        }

        $this->info('Audit complete! Results saved to database.');

        return Command::SUCCESS;
    }
}
