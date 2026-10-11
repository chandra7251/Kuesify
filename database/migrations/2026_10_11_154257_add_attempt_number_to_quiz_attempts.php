<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->unsignedInteger('attempt_number')->nullable()->after('participant_id');
            $table->unique(['quiz_id', 'participant_id', 'attempt_number'], 'quiz_attempts_unique_attempt_number');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->dropUnique('quiz_attempts_unique_attempt_number');
            $table->dropColumn('attempt_number');
        });
    }
};
