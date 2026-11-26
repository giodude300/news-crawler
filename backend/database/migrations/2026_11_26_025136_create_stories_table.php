<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processing_execution_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('hacker_news_id');
            $table->string('title');
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedSmallInteger('word_count');
            $table->string('filter_type', 20);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique([
                'processing_execution_id',
                'hacker_news_id',
            ]);

            $table->index([
                'processing_execution_id',
                'filter_type',
                'position',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('stories');
    }
};
