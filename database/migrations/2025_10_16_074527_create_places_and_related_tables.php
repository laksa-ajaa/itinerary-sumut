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
        // PLACES (wisata)
        Schema::create('places', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('google_place_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->float('rating')->nullable();
            $table->unsignedInteger('rating_count')->default(0);

            $table->json('types')->nullable();       // google place types
            $table->json('photos')->nullable();
            $table->json('opening_hours')->nullable();

            $table->string('city')->nullable();
            $table->string('address')->nullable();

            $table->timestamps();
        });

        // RESTAURANTS
        Schema::create('restaurants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('google_place_id')->unique();
            $table->string('name');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->float('rating')->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedTinyInteger('price_level')->nullable();

            $table->json('types')->nullable();
            $table->json('photos')->nullable();
            $table->json('opening_hours')->nullable();

            $table->string('address')->nullable();

            $table->timestamps();
        });

        // ACCOMMODATIONS
        Schema::create('accommodations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('google_place_id')->unique();
            $table->string('name');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->float('rating')->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedTinyInteger('price_level')->nullable();

            $table->json('types')->nullable();
            $table->json('photos')->nullable();
            $table->json('opening_hours')->nullable();

            $table->string('address')->nullable();

            $table->timestamps();
        });

        // CATEGORIES (for places)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('emoji', 8)->nullable();
            $table->timestamps();
        });

        // place_category pivot (places <-> categories)
        Schema::create('place_category', function (Blueprint $table) {
            $table->uuid('place_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['place_id', 'category_id']);

            // optional FK constraints:
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        // google type mappings (category -> google_type)
        Schema::create('place_category_google_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('google_type');
            $table->unique(['category_id', 'google_type']);
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->timestamps();
        });

        // user_ratings (for CF)
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('place_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete();
            $table->index(['user_id', 'place_id']);
        });

        // ITINERARIES (header)
        Schema::create('itineraries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('start_date')->nullable();
            $table->unsignedInteger('day_count')->default(1);
            $table->enum('budget_level', ['hemat', 'sedang', 'premium'])->default('sedang');
            $table->enum('activity_level', ['santai', 'normal', 'padat'])->default('normal');
            $table->json('preferences')->nullable(); // store raw preferences
            $table->json('generated_payload')->nullable(); // optional store final json
            $table->timestamps();
        });

        // ITINERARY_ITEMS (polymorphic-ish)
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('itinerary_id');
            $table->unsignedInteger('day')->default(1);

            // polymorphic item
            $table->uuid('item_id');
            $table->string('item_type'); // 'place' | 'restaurant' | 'accommodation'

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->foreign('itinerary_id')->references('id')->on('itineraries')->cascadeOnDelete();
            $table->index(['itinerary_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('user_ratings');
        Schema::dropIfExists('place_category_google_types');
        Schema::dropIfExists('place_category');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('restaurants');
        Schema::dropIfExists('places');
    }
};
