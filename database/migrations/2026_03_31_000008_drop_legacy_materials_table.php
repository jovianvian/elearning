<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('materials');
    }

    public function down(): void
    {
        // Materials table is deprecated in Teramia lock.
    }
};
