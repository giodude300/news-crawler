<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'New Crawler API',
    description: 'REST API for crawling, filtering, sorting, and tracking stories.',
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local development server',
)]
#[OA\Tag(
    name: 'Stories',
    description: 'Story processing operations',
)]
#[OA\Tag(
    name: 'Executions',
    description: 'Processing execution history',
)]
#[OA\Schema(
    schema: 'Story',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 88654321),
        new OA\Property(property: 'title', type: 'string', example: 'Example story title'),
        new OA\Property(property: 'points', type: 'integer', example: 125),
        new OA\Property(property: 'comments', type: 'integer', example: 42),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ProcessingExecutionSummary',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'requested_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'stories_count', type: 'integer', example: 30),
        new OA\Property(property: 'duration_ms', type: 'integer', example: 1250),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ProcessingExecution',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'requested_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'filters_applied',
            type: 'array',
            items: new OA\Items(type: 'string', enum: ['long_titles', 'short_titles']),
            example: ['long_titles', 'short_titles'],
        ),
        new OA\Property(property: 'stories_count', type: 'integer', example: 30),
        new OA\Property(property: 'duration_ms', type: 'integer', example: 1250),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
class OpenApiSpec {
}
