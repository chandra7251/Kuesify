<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_question_drafts', function (Blueprint $table): void {
            $table->id(); $table->foreignId('ai_generation_id')->constrained()->cascadeOnDelete();
            $table->string('type'); $table->text('prompt'); $table->json('options')->nullable(); $table->text('correct_answer')->nullable();
            $table->text('explanation')->nullable(); $table->unsignedInteger('points')->default(1000); $table->string('status'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_question_drafts'); }
};
