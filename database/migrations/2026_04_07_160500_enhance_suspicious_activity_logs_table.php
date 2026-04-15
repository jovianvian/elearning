<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suspicious_activity_logs', function (Blueprint $table): void {
            $table->unsignedInteger('event_count')->default(1)->after('note');
            $table->timestamp('last_detected_at')->nullable()->after('event_count');
            $table->json('context_json')->nullable()->after('last_detected_at');
        });
    }

    public function down(): void
    {
        Schema::table('suspicious_activity_logs', function (Blueprint $table): void {
            $table->dropColumn(['event_count', 'last_detected_at', 'context_json']);
        });
    }
};

