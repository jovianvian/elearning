<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('religion', 50)->nullable()->after('birth_place');
            $table->string('source_class_name', 100)->nullable()->after('religion');
            $table->string('parent_name')->nullable()->after('source_class_name');
            $table->string('parent_birth_year', 10)->nullable()->after('parent_name');
            $table->string('parent_education', 100)->nullable()->after('parent_birth_year');
            $table->string('employment_status', 100)->nullable()->after('parent_education');
            $table->string('ptk_type', 100)->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'birth_place',
                'religion',
                'source_class_name',
                'parent_name',
                'parent_birth_year',
                'parent_education',
                'employment_status',
                'ptk_type',
            ]);
        });
    }
};

