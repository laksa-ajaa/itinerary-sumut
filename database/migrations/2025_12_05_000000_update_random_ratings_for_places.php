<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update rating_avg dengan nilai random antara 4.0 - 5.0
        // Update rating_count dengan nilai random antara 500 - 2000
        DB::statement("
            UPDATE places 
            SET 
                rating_avg = 4.0 + (random() * 1.0),
                rating_count = 500 + floor(random() * 1501)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset rating_avg dan rating_count ke NULL atau nilai default
        // Note: Tidak bisa mengembalikan nilai sebelumnya karena random
        DB::statement("
            UPDATE places 
            SET 
                rating_avg = NULL,
                rating_count = 0
        ");
    }
};
