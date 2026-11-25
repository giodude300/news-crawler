<?php

namespace App\Services;

use App\Models\ProcessingExecution;

class StoryProcessingService {
    public function __construct(private HackerNewsCrawler $crawler, private StoryProcessor $processor) {
    }

    public function process(): array {
        $requestedAt = now();
        $startTime = hrtime(true);
        $stories = $this->crawler->crawl();
        $results = $this->processor->process($stories);
        $duration = (int) ((hrtime(true) - $startTime) / 1_000_000);

        $execution = ProcessingExecution::create([
            'requested_at' => $requestedAt,
            'filters_applied' => ['long_titles', 'short_titles'],
            'stories_count' => count($stories),
            'duration_ms' => $duration,
        ]);

        return [
            'long_titles' => $results['long_titles'],
            'short_titles' => $results['short_titles'],
            'execution' => [
                'id' => $execution->id,
                'requested_at' => $execution->requested_at,
                'stories_count' => $execution->stories_count,
                'duration_ms' => $execution->duration_ms,
            ],
        ];
    }
}
