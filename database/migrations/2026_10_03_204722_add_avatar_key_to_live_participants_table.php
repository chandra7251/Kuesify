<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_participants', function (Blueprint $table): void {
            $table->string('avatar_key')->nullable()->after('alias');
        });
    }

    public function down(): void
    {
        Schema::table('live_participants', function (Blueprint $table): void {
            $table->dropColumn('avatar_key');
        });
    }
};
