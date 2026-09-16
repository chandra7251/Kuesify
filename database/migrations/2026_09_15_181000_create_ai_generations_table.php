<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table): void {
            $table->id(); $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete(); $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('status'); $table->unsignedTinyInteger('question_count'); $table->string('difficulty'); $table->json('types');
            $table->string('failure_reason')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_generations'); }
};
