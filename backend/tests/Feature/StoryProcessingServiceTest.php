<?php

namespace Tests\Feature;

use App\Models\ProcessingExecution;
use App\Services\HackerNewsCrawler;
use App\Services\StoryProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class StoryProcessingServiceTest extends TestCase {
    use RefreshDatabase;

    public function test_processing_stories_persists_execution_and_classified_stories(): void {
        $stories = [
            [
                'id' => 1,
                'title' => 'Laravel makes development enjoyable',
                'points' => 10,
                'comments' => 2,
            ],
            [
                'id' => 2,
                'title' => 'Understanding distributed systems through practical examples',
                'points' => 20,
                'comments' => 15,
            ],
        ];

        $this->mock(HackerNewsCrawler::class, function (MockInterface $mock) use ($stories) {
            $mock->shouldReceive('crawl')
                ->once()
                ->andReturn($stories);
        });

        $result = app(StoryProcessingService::class)->process();
        $this->assertCount(1, ProcessingExecution::all());
        $execution = ProcessingExecution::first();

        $this->assertSame(['long_titles', 'short_titles'], $execution->filters_applied);
        $this->assertSame(2, $execution->stories_count);
        $this->assertGreaterThanOrEqual(0, $execution->duration_ms);
        $this->assertNotNull($execution->requested_at);

        $this->assertCount(1, $result['long_titles']);
        $this->assertCount(1, $result['short_titles']);

        $this->assertSame($execution->id, $result['execution']['id']);
        $this->assertSame(2, $result['execution']['stories_count']);
        $this->assertDatabaseCount('stories', 2);

        $this->assertDatabaseHas('stories', [
            'processing_execution_id' => $execution->id,
            'hacker_news_id' => 1,
            'title' => 'Laravel makes development enjoyable',
            'points' => 10,
            'comments' => 2,
            'word_count' => 4,
            'filter_type' => 'short_titles',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('stories', [
            'processing_execution_id' => $execution->id,
            'hacker_news_id' => 2,
            'title' => 'Understanding distributed systems through practical examples',
            'points' => 20,
            'comments' => 15,
            'word_count' => 6,
            'filter_type' => 'long_titles',
            'position' => 1,
        ]);

        $this->assertCount(2, $execution->stories);
    }
}
