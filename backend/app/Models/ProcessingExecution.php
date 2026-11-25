<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessingExecution extends Model {
    protected $fillable = [
        'requested_at',
        'filters_applied',
        'stories_count',
        'duration_ms',
    ];

    protected function casts(): array {
        return [
            'requested_at' => 'datetime',
            'filters_applied' => 'array',
            'stories_count' => 'integer',
            'duration_ms' => 'integer',
        ];
    }
}
