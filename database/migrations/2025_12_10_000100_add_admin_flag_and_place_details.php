<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin flag for users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('password');
            }
        });

        // Extra metadata for places
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'website')) {
                $table->string('website')->nullable()->after('rating_count');
            }
            if (!Schema::hasColumn('places', 'contact')) {
                $table->string('contact')->nullable()->after('website');
            }
            if (!Schema::hasColumn('places', 'opening_hours')) {
                $table->json('opening_hours')->nullable()->after('contact');
            }
            if (!Schema::hasColumn('places', 'facilities')) {
                $table->json('facilities')->nullable()->after('opening_hours');
            }
            if (!Schema::hasColumn('places', 'rating_avg')) {
                $table->float('rating_avg')->nullable()->after('rating');
            }
        });

        // Extra metadata for restaurants
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'website')) {
                $table->string('website')->nullable()->after('rating_count');
            }
            if (!Schema::hasColumn('restaurants', 'contact')) {
                $table->string('contact')->nullable()->after('website');
            }
            if (!Schema::hasColumn('restaurants', 'opening_hours')) {
                $table->json('opening_hours')->nullable()->after('contact');
            }
            if (!Schema::hasColumn('restaurants', 'facilities')) {
                $table->json('facilities')->nullable()->after('opening_hours');
            }
            if (!Schema::hasColumn('restaurants', 'rating_avg')) {
                $table->float('rating_avg')->nullable()->after('rating');
            }
        });

        // Extra metadata for accommodations
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'website')) {
                $table->string('website')->nullable()->after('rating_count');
            }
            if (!Schema::hasColumn('accommodations', 'contact')) {
                $table->string('contact')->nullable()->after('website');
            }
            if (!Schema::hasColumn('accommodations', 'opening_hours')) {
                $table->json('opening_hours')->nullable()->after('contact');
            }
            if (!Schema::hasColumn('accommodations', 'facilities')) {
                $table->json('facilities')->nullable()->after('opening_hours');
            }
            if (!Schema::hasColumn('accommodations', 'rating_avg')) {
                $table->float('rating_avg')->nullable()->after('rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });

        Schema::table('places', function (Blueprint $table) {
            foreach (['website', 'contact', 'opening_hours', 'facilities', 'rating_avg'] as $column) {
                if (Schema::hasColumn('places', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('restaurants', function (Blueprint $table) {
            foreach (['website', 'contact', 'opening_hours', 'facilities', 'rating_avg'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('accommodations', function (Blueprint $table) {
            foreach (['website', 'contact', 'opening_hours', 'facilities', 'rating_avg'] as $column) {
                if (Schema::hasColumn('accommodations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

