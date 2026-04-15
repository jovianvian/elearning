<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempt_answers', function (Blueprint $table): void {
            $table->json('selected_option_ids_json')->nullable()->after('selected_option_id');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempt_answers', function (Blueprint $table): void {
            $table->dropColumn('selected_option_ids_json');
        });
    }
};

