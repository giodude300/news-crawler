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
            ]);

        $this->assertDatabaseHas('processing_executions', [
            'id' => $response->json('execution.id'),
            'stories_count' => 2,
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
            ->assertJsonPath('stories_count', $execution->stories_count);
    }

    public function test_not_found_is_returned_for_nonexistent_execution(): void {
        $response = $this->getJson('/api/executions/99999');
        $response->assertNotFound();
    }
}
