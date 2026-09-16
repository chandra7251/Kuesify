<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->timestamp('deadline_at')->nullable()->after('status');
            $table->unsignedInteger('max_attempts')->nullable()->after('deadline_at');
            $table->boolean('show_explanations')->default(false)->after('max_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn(['deadline_at', 'max_attempts', 'show_explanations']);
        });
    }
};
