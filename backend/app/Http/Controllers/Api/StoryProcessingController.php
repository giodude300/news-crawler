<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessingExecution;
use App\Services\StoryProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryProcessingController extends Controller {
    public function __construct(private StoryProcessingService $storyProcessingService) {
    }

    /**
     * Crawl Hacker News and process the retrieved stories.
     */
    public function process(): JsonResponse {
        $results = $this->storyProcessingService->process();
        return response()->json($results);
    }

    /**
     * Retrieve processing execution history with pagination.
     */
    public function index(Request $request): JsonResponse {
        // Default to 30 records and reject values outside the allowed range.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ]);

        $perPage = $validated['per_page'] ?? 30;
        $executions = ProcessingExecution::query()
            ->latest('requested_at')
            ->paginate($perPage);

        return response()->json($executions);
    }

    /**
     * Retrieve a specific processing execution.
     */
    public function show(int $id): JsonResponse {
        $execution = ProcessingExecution::findOrFail($id);
        return response()->json($execution);
    }
}
