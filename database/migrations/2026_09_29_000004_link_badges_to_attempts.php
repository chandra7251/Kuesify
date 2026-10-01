<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_awards', function (Blueprint $table): void {
            $table->foreignId('quiz_attempt_id')->nullable()->after('badge_id')->constrained('quiz_attempts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('badge_awards', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('quiz_attempt_id');
        });
    }
};
