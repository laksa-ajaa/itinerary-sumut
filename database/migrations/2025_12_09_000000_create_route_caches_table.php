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
        Schema::create('route_caches', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->decimal('from_lat', 10, 7);
            $table->decimal('from_lng', 10, 7);
            $table->decimal('to_lat', 10, 7);
            $table->decimal('to_lng', 10, 7);
            $table->string('provider')->default('mapbox');
            $table->string('profile')->default('mapbox/driving');
            $table->double('distance_meters');
            $table->double('duration_seconds')->nullable();
            $table->json('coordinates'); // [[lat, lng], ...]
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_caches');
    }
};
