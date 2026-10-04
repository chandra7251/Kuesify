<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table): void {
            $table->string('description')->nullable()->after('name');
            $table->string('rarity')->default('common')->after('description');
            $table->string('criteria_type')->default('completed_attempts')->after('rarity');
            $table->unsignedInteger('criteria_value')->default(1)->after('criteria_type');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table): void {
            $table->dropColumn(['description', 'rarity', 'criteria_type', 'criteria_value']);
        });
    }
};
