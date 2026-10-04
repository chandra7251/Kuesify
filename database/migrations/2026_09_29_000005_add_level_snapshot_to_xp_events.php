<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xp_events', function (Blueprint $table): void {
            $table->unsignedInteger('level_before')->nullable()->after('amount');
            $table->unsignedInteger('level_after')->nullable()->after('level_before');
        });
    }

    public function down(): void
    {
        Schema::table('xp_events', function (Blueprint $table): void {
            $table->dropColumn(['level_before', 'level_after']);
        });
    }
};
