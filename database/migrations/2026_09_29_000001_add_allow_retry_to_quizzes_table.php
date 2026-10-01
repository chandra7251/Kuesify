<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('allow_retry')->default(false)->after('max_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn('allow_retry');
        });
    }
};