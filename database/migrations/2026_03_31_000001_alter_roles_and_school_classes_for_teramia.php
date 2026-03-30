<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->string('code', 50)->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->boolean('is_active')->default(true)->after('description');
        });

        Schema::table('school_classes', function (Blueprint $table): void {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('homeroom_teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table): void {
            $table->dropColumn('academic_year_id');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn(['code', 'description', 'is_active']);
        });
    }
};
