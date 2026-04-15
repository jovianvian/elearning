<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_bills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'semester_id'], 'student_bill_unique_scope');
            $table->index(['status', 'student_id']);
        });

        Schema::create('bill_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_bill_id')->constrained('student_bills')->cascadeOnDelete();
            $table->unsignedTinyInteger('month_number');
            $table->string('month_name', 20);
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['student_bill_id', 'month_number'], 'bill_item_unique_month');
            $table->index(['month_number', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('student_bills');
    }
};

