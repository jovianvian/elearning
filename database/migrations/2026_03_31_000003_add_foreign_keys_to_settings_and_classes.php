<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table): void {
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
        });

        Schema::table('app_settings', function (Blueprint $table): void {
            $table->foreign('active_academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('active_semester_id')->references('id')->on('semesters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table): void {
            $table->dropForeign(['active_academic_year_id']);
            $table->dropForeign(['active_semester_id']);
        });

        Schema::table('school_classes', function (Blueprint $table): void {
            $table->dropForeign(['academic_year_id']);
        });
    }
};
