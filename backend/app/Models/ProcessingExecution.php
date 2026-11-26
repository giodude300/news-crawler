<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessingExecution extends Model {
    use HasFactory;

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

    public function stories(): HasMany {
        return $this->hasMany(Story::class);
    }
}
