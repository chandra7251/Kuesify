<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_progresses', function (Blueprint $table): void {
            $table->id(); $table->foreignId('organization_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('xp')->default(0); $table->unsignedInteger('level')->default(1); $table->unsignedInteger('streak')->default(0); $table->date('last_activity_date')->nullable(); $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });
        Schema::create('xp_events', function (Blueprint $table): void {
            $table->id(); $table->foreignId('organization_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete(); $table->unsignedInteger('amount'); $table->timestamps(); $table->unique('quiz_attempt_id');
        });
        Schema::create('badges', function (Blueprint $table): void { $table->id(); $table->string('key')->unique(); $table->string('name'); $table->timestamps(); });
        Schema::create('badge_awards', function (Blueprint $table): void {
            $table->id(); $table->foreignId('organization_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('badge_id')->constrained()->cascadeOnDelete(); $table->timestamps();
            $table->unique(['organization_id', 'user_id', 'badge_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('badge_awards'); Schema::dropIfExists('badges'); Schema::dropIfExists('xp_events'); Schema::dropIfExists('user_progresses'); }
};
