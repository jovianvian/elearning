<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->unsignedTinyInteger('required_paid_month')->nullable()->after('max_attempts');
            $table->index('required_paid_month');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->dropIndex(['required_paid_month']);
            $table->dropColumn('required_paid_month');
        });
    }
};

