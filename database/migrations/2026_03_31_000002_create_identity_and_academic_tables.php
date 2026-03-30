<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('photo')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('preferred_language', 5)->default('id');
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('login_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->boolean('is_success')->default(false);
            $table->string('failure_reason')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('app_name')->default('Teramia E-Learning');
            $table->string('school_name')->default('SMP Teramia');
            $table->string('school_logo')->nullable();
            $table->string('school_favicon')->nullable();
            $table->string('primary_color', 20)->default('#1D4ED8');
            $table->string('secondary_color', 20)->default('#1E3A8A');
            $table->string('accent_color', 20)->default('#FACC15');
            $table->string('default_locale', 5)->default('id');
            $table->json('supported_locales_json')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('school_email')->nullable();
            $table->string('school_phone', 50)->nullable();
            $table->text('school_address')->nullable();
            $table->unsignedBigInteger('active_academic_year_id')->nullable();
            $table->unsignedBigInteger('active_semester_id')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name', 30);
            $table->string('code', 20);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['academic_year_id', 'code']);
        });

        Schema::create('class_students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id']);
        });

        Schema::create('subject_teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['teacher_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_teachers');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('user_profiles');
    }
};
