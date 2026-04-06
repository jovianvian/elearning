<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->decimal('target_score', 8, 2)->default(100)->after('max_attempts');
            $table->decimal('objective_weight_percent', 5, 2)->default(60)->after('target_score');
            $table->decimal('essay_weight_percent', 5, 2)->default(40)->after('objective_weight_percent');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn(['target_score', 'objective_weight_percent', 'essay_weight_percent']);
        });
    }
};

