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
        // Update places table for OSM-based data
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'osm_id')) {
                $table->string('osm_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('places', 'osm_type')) {
                $table->string('osm_type')->nullable()->after('osm_id');
            }

            if (!Schema::hasColumn('places', 'source')) {
                $table->string('source')->default('osm')->after('osm_type');
            }

            if (!Schema::hasColumn('places', 'tags')) {
                $table->json('tags')->nullable()->after('types');
            }

            // Unique index for OSM identity if both columns exist
            if (Schema::hasColumn('places', 'osm_id') && Schema::hasColumn('places', 'osm_type')) {
                $table->unique(['osm_id', 'osm_type'], 'places_osm_identity_unique');
            }
        });

        Schema::table('places', function (Blueprint $table) {
            // Drop Google Places specific columns if they exist
            if (Schema::hasColumn('places', 'google_place_id')) {
                // Some drivers require dropping unique index explicitly
                try {
                    $table->dropUnique('places_google_place_id_unique');
                } catch (\Throwable $e) {
                    // ignore if index name does not exist
                }

                $table->dropColumn('google_place_id');
            }

            if (Schema::hasColumn('places', 'types')) {
                $table->dropColumn('types');
            }

            if (Schema::hasColumn('places', 'photos')) {
                $table->dropColumn('photos');
            }

            if (Schema::hasColumn('places', 'opening_hours')) {
                $table->dropColumn('opening_hours');
            }
        });

        // Drop Google-specific helper tables that are no longer used
        Schema::dropIfExists('place_category_google_types');
        Schema::dropIfExists('restaurants');
        Schema::dropIfExists('accommodations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate dropped tables in minimal form to allow rollback
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

        Schema::create('place_category_google_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('google_type');
            $table->unique(['category_id', 'google_type'], 'pcgt_category_google_type_unique');
            $table->timestamps();
        });

        Schema::table('places', function (Blueprint $table) {
            // Re-add Google columns
            if (!Schema::hasColumn('places', 'google_place_id')) {
                $table->string('google_place_id')->nullable();
            }

            if (!Schema::hasColumn('places', 'types')) {
                $table->json('types')->nullable();
            }

            if (!Schema::hasColumn('places', 'photos')) {
                $table->json('photos')->nullable();
            }

            if (!Schema::hasColumn('places', 'opening_hours')) {
                $table->json('opening_hours')->nullable();
            }

            // Drop OSM columns
            if (Schema::hasColumn('places', 'tags')) {
                $table->dropColumn('tags');
            }

            if (Schema::hasColumn('places', 'source')) {
                $table->dropColumn('source');
            }

            // Drop unique index before removing columns
            try {
                $table->dropUnique('places_osm_identity_unique');
            } catch (\Throwable $e) {
                // ignore
            }

            if (Schema::hasColumn('places', 'osm_type')) {
                $table->dropColumn('osm_type');
            }
            if (Schema::hasColumn('places', 'osm_id')) {
                $table->dropColumn('osm_id');
            }
        });
    }
};


