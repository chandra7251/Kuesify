<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('request_id', 36)->index();

            $table->string('method', 10);
            $table->text('url');

            $table->unsignedSmallInteger('status_code');
            $table->unsignedInteger('duration_ms')->default(0);

            $table->string('ip', 45);
            $table->string('user_agent', 500)->nullable();

            $table->json('request_body')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_crash_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('request_id', 36)->index();

            $table->string('method', 10);
            $table->text('url');

            $table->unsignedSmallInteger('status_code')->default(500);

            $table->text('message');
            $table->text('trace')->nullable();

            $table->json('request_body')->nullable();

            $table->string('ip', 45);
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_crash_logs');
        Schema::dropIfExists('platform_activity_logs');
    }
};
