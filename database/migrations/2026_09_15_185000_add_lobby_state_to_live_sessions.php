<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void { $table->boolean('lobby_locked')->default(false)->after('status'); });
        Schema::table('live_participants', function (Blueprint $table): void { $table->timestamp('kicked_at')->nullable()->after('score'); });
    }
    public function down(): void
    {
        Schema::table('live_participants', function (Blueprint $table): void { $table->dropColumn('kicked_at'); });
        Schema::table('live_sessions', function (Blueprint $table): void { $table->dropColumn('lobby_locked'); });
    }
};
