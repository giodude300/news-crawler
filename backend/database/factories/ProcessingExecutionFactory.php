<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProcessingExecution>
 */
class ProcessingExecutionFactory extends Factory {
    public function definition(): array {
        return [
            'requested_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'filters_applied' => ['long_titles', 'short_titles'],
            'stories_count' => fake()->numberBetween(1, 30),
            'duration_ms' => fake()->numberBetween(10, 5000),
        ];
    }
}
