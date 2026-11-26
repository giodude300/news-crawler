<?php

namespace App\Services;

use App\Models\ProcessingExecution;
use Illuminate\Support\Facades\DB;

class StoryProcessingService {
    public function __construct(private HackerNewsCrawler $crawler, private StoryProcessor $processor) {
    }

    public function process(): array {
        $requestedAt = now();
        $startTime = hrtime(true);
        $stories = $this->crawler->crawl();
        $results = $this->processor->process($stories);
        $duration = (int) ((hrtime(true) - $startTime) / 1_000_000);

        $execution = DB::transaction(function () use ($requestedAt, $duration, $stories, $results) {
            $execution = ProcessingExecution::create([
                'requested_at' => $requestedAt,
                'filters_applied' => ['long_titles', 'short_titles'],
                'stories_count' => count($stories),
                'duration_ms' => $duration,
            ]);

            $this->saveStories(
                $execution,
                $results['long_titles'],
                'long_titles',
            );

            $this->saveStories(
                $execution,
                $results['short_titles'],
                'short_titles',
            );

            return $execution;
        });

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

    private function saveStories(ProcessingExecution $execution, array $stories, string $filterType): void {
        $records = [];
        foreach ($stories as $index => $story) {
            $records[] = [
                'hacker_news_id' => $story['id'],
                'title' => $story['title'],
                'points' => $story['points'],
                'comments' => $story['comments'],
                'word_count' => $this->processor->countWords($story['title']),
                'filter_type' => $filterType,
                'position' => $index + 1,
            ];
        }

        $execution->stories()->createMany($records);
    }
}
