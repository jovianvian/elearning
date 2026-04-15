<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('type', 20);
            $table->string('external_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'is_published']);
            $table->index(['type', 'is_published']);
            $table->index(['sort_order', 'id']);
        });

        Schema::create('student_material_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_material_id')->constrained('learning_materials')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('not_started');
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['learning_material_id', 'student_id'], 'material_progress_unique');
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_material_progress');
        Schema::dropIfExists('learning_materials');
    }
};

