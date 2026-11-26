<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessingExecution;
use App\Services\StoryProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StoryProcessingController extends Controller {
    public function __construct(private StoryProcessingService $storyProcessingService) {
    }

    /**
     * Crawl Hacker News and process the retrieved stories.
     */
    #[OA\Post(
        path: '/api/stories/process',
        operationId: 'processStories',
        tags: ['Stories'],
        summary: 'Process Hacker News stories',
        description: 'Crawls the latest stories, applies both filters, sorts the results, and stores the execution.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Stories processed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'long_titles',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Story'),
                        ),
                        new OA\Property(
                            property: 'short_titles',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Story'),
                        ),
                        new OA\Property(
                            property: 'execution',
                            ref: '#/components/schemas/ProcessingExecutionSummary',
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 500,
                description: 'Unable to process stories',
            ),
        ],
    )]
    public function process(): JsonResponse {
        $results = $this->storyProcessingService->process();
        return response()->json($results);
    }

    /**
     * Retrieve processing execution history with pagination.
     */
    #[OA\Get(
        path: '/api/executions',
        operationId: 'getExecutions',
        tags: ['Executions'],
        summary: 'Get processing execution history',
        description: 'Returns processing executions ordered from newest to oldest, including the count of long and short titles.',
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of executions per page. Defaults to 30 and cannot exceed 30.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 30,
                    default: 30,
                ),
            ),
            new OA\Parameter(
                name: 'page',
                description: 'The page number to retrieve.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    default: 1,
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Execution history retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'requested_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'filters_applied', type: 'object'),
                                    new OA\Property(property: 'stories_count', type: 'integer'),
                                    new OA\Property(property: 'long_titles_count', type: 'integer'),
                                    new OA\Property(property: 'short_titles_count', type: 'integer'),
                                    new OA\Property(property: 'duration_ms', type: 'integer', nullable: true),
                                ],
                                type: 'object',
                            ),
                        ),
                        new OA\Property(
                            property: 'links',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', nullable: true),
                                new OA\Property(property: 'last', type: 'string', nullable: true),
                                new OA\Property(property: 'prev', type: 'string', nullable: true),
                                new OA\Property(property: 'next', type: 'string', nullable: true),
                            ],
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'from', type: 'integer', nullable: true),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'to', type: 'integer', nullable: true),
                                new OA\Property(property: 'total', type: 'integer'),
                            ],
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid pagination parameters',
            ),
        ],
    )]
    public function index(Request $request): JsonResponse {
        // Default to 30 records and reject values outside the allowed range.
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 30;
        $executions = ProcessingExecution::query()
            ->withCount([
                'stories as long_titles_count' => function ($query) {
                    $query->where('filter_type', 'long_titles');
                },
                'stories as short_titles_count' => function ($query) {
                    $query->where('filter_type', 'short_titles');
                },
            ])
            ->latest('requested_at')
            ->paginate($perPage);

        return response()->json($executions);
    }

    /**
     * Retrieve a specific processing execution, including its stories.
     */
    #[OA\Get(
        path: '/api/executions/{id}',
        operationId: 'getExecution',
        tags: ['Executions'],
        summary: 'Get a specific processing execution',
        description: 'Returns the execution details, the count of long and short titles, and all associated stories. Stories can optionally be filtered by title length category.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Processing execution ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'filter',
                description: 'Filter stories by title length category.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['long_titles', 'short_titles'],
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Execution retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'requested_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'filters_applied', type: 'object'),
                        new OA\Property(property: 'stories_count', type: 'integer'),
                        new OA\Property(property: 'long_titles_count', type: 'integer'),
                        new OA\Property(property: 'short_titles_count', type: 'integer'),
                        new OA\Property(property: 'duration_ms', type: 'integer', nullable: true),
                        new OA\Property(
                            property: 'stories',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Story'),
                        ),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Execution not found',
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid filter parameter',
            ),
        ],
    )]
    public function show(Request $request, int $id): JsonResponse {
        $validated = $request->validate([
            'filter' => ['sometimes', 'string', 'in:long_titles,short_titles'],
        ]);

        $execution = ProcessingExecution::query()
            ->withCount([
                'stories as long_titles_count' => function ($query) {
                    $query->where('filter_type', 'long_titles');
                },
                'stories as short_titles_count' => function ($query) {
                    $query->where('filter_type', 'short_titles');
                },
            ])
            ->with([
                'stories' => function ($query) use ($validated) {
                    $query
                        ->when(
                            isset($validated['filter']),
                            fn ($query) => $query->where('filter_type', $validated['filter']),
                        )
                        ->orderBy('filter_type')
                        ->orderBy('position');
                },
            ])
            ->findOrFail($id);

        return response()->json($execution);
    }
}
