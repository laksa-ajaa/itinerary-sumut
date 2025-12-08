<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'kind')) {
                $table->string('kind')->nullable()->after('name');
            }

            if (!Schema::hasColumn('places', 'rating_avg')) {
                $table->float('rating_avg')->nullable()->after('rating');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (Schema::hasColumn('places', 'rating_avg')) {
                $table->dropColumn('rating_avg');
            }
            if (Schema::hasColumn('places', 'kind')) {
                $table->dropColumn('kind');
            }
        });
    }
};

