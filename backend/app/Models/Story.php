<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Story extends Model {
    protected $fillable = [
        'processing_execution_id',
        'hacker_news_id',
        'title',
        'points',
        'comments',
        'word_count',
        'filter_type',
        'position',
    ];

    protected function casts(): array {
        return [
            'hacker_news_id' => 'integer',
            'points' => 'integer',
            'comments' => 'integer',
            'word_count' => 'integer',
            'position' => 'integer',
        ];
    }

    public function execution(): BelongsTo {
        return $this->belongsTo(ProcessingExecution::class, 'processing_execution_id');
    }
}
