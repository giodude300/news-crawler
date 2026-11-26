<?php

namespace Tests\Feature;

use App\Models\ProcessingExecution;
use App\Services\HackerNewsCrawler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class StoryProcessingApiTest extends TestCase {
    use RefreshDatabase;

    public function test_stories_can_be_processed_through_the_api(): void {
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
            $mock->shouldReceive('crawl')->once()->andReturn($stories);
        });

        $response = $this->postJson('/api/stories/process');

        $response->assertOk()
            ->assertJsonStructure([
                'long_titles',
                'short_titles',
                'execution' => [
                    'id',
                    'requested_at',
                    'stories_count',
                    'duration_ms',
                ],
            ])
            ->assertJsonPath('execution.stories_count', 2)
            ->assertJsonPath('long_titles.0.id', 2)
            ->assertJsonPath('short_titles.0.id', 1);

        $executionId = $response->json('execution.id');

        $this->assertDatabaseHas('processing_executions', [
            'id' => $executionId,
            'stories_count' => 2,
        ]);

        $this->assertDatabaseCount('stories', 2);
        $this->assertDatabaseHas('stories', [
            'processing_execution_id' => $executionId,
            'hacker_news_id' => 1,
            'filter_type' => 'short_titles',
            'word_count' => 4,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('stories', [
            'processing_execution_id' => $executionId,
            'hacker_news_id' => 2,
            'filter_type' => 'long_titles',
            'word_count' => 6,
            'position' => 1,
        ]);
    }

    public function test_execution_history_returns_30_records_by_default(): void {
        ProcessingExecution::factory()->count(35)->create();

        $response = $this->getJson('/api/executions');

        $response->assertOk()
            ->assertJsonPath('per_page', 30)
            ->assertJsonPath('total', 35)
            ->assertJsonCount(30, 'data');
    }

    public function test_execution_history_accepts_custom_page_size(): void {
        ProcessingExecution::factory()->count(35)->create();

        $response = $this->getJson('/api/executions?per_page=5');

        $response->assertOk()
            ->assertJsonPath('per_page', 5)
            ->assertJsonPath('total', 35)
            ->assertJsonCount(5, 'data');
    }

    public function test_execution_history_supports_page_navigation(): void {
        ProcessingExecution::factory()->count(35)->create();

        $response = $this->getJson('/api/executions?per_page=5&page=2');

        $response->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('per_page', 5)
            ->assertJsonCount(5, 'data');
    }

    public function test_execution_history_is_ordered_from_newest_to_oldest(): void {
        $olderExecution = ProcessingExecution::factory()->create([
            'requested_at' => now()->subHour(),
        ]);
        $newerExecution = ProcessingExecution::factory()->create([
            'requested_at' => now(),
        ]);
        $response = $this->getJson('/api/executions');
        $response->assertOk()
            ->assertJsonPath('data.0.id', $newerExecution->id)
            ->assertJsonPath('data.1.id', $olderExecution->id);
    }

    public function test_execution_history_rejects_page_sizes_greater_than_30(): void {
        $response = $this->getJson('/api/executions?per_page=31');

        $response->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_execution_history_rejects_invalid_page_sizes(): void {
        $response = $this->getJson('/api/executions?per_page=invalid');

        $response->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_specific_execution_can_be_retrieved(): void {
        $execution = ProcessingExecution::factory()->create();

        $response = $this->getJson("/api/executions/{$execution->id}");

        $response->assertOk()
            ->assertJsonPath('id', $execution->id)
            ->assertJsonPath('stories_count', $execution->stories_count)
            ->assertJsonPath('filters_applied', ['long_titles', 'short_titles']);
    }

    public function test_not_found_is_returned_for_nonexistent_execution(): void {
        $response = $this->getJson('/api/executions/99999');
        $response->assertNotFound();
    }
}
