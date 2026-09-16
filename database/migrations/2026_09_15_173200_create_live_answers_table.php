<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('answer');
            $table->boolean('is_correct');
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamps();
            $table->unique(['live_participant_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_answers');
    }
};
