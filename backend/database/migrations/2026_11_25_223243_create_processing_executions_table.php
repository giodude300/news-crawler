<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('processing_executions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('requested_at');
            $table->json('filters_applied');
            $table->unsignedSmallInteger('stories_count');
            $table->unsignedInteger('duration_ms');
            $table->timestamps();
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('processing_executions');
    }
};
