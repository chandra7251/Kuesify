<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('creator_id')->constrained()->nullOnDelete();
            $table->text('description')->nullable()->after('title');
            $table->string('visibility')->default('private')->after('status');
            $table->string('cover_image')->nullable()->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['description', 'visibility', 'cover_image']);
        });
    }
};
