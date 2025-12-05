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
        // RESTAURANTS (OSM-based)
        if (!Schema::hasTable('restaurants')) {
            Schema::create('restaurants', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->string('osm_id')->nullable();
                $table->string('osm_type')->nullable();
                $table->string('source')->default('osm');

                $table->string('name')->nullable();
                $table->string('kind')->nullable(); // restoran, cafe, dll (opsional)

                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();

                $table->float('rating')->nullable();
                $table->float('rating_avg')->nullable();
                $table->unsignedInteger('rating_count')->default(0);

                $table->string('city')->nullable();
                $table->string('address')->nullable();

                $table->json('tags')->nullable(); // semua tag OSM mentah

                $table->timestamps();

                $table->unique(['osm_id', 'osm_type'], 'restaurants_osm_identity_unique');
            });
        }

        // ACCOMMODATIONS (OSM-based)
        if (!Schema::hasTable('accommodations')) {
            Schema::create('accommodations', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->string('osm_id')->nullable();
                $table->string('osm_type')->nullable();
                $table->string('source')->default('osm');

                $table->string('name')->nullable();
                $table->string('kind')->nullable(); // penginapan, hotel, guest_house, dll

                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();

                $table->float('rating')->nullable();
                $table->float('rating_avg')->nullable();
                $table->unsignedInteger('rating_count')->default(0);

                $table->string('city')->nullable();
                $table->string('address')->nullable();

                $table->json('tags')->nullable();

                $table->timestamps();

                $table->unique(['osm_id', 'osm_type'], 'accommodations_osm_identity_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('restaurants');
    }
};


