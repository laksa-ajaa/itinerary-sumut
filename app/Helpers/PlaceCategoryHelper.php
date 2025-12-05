<?php

namespace App\Helpers;

class PlaceCategoryHelper
{
    /**
     * Daftar kategori wisata yang digunakan dalam sistem
     * Sesuai dengan mapping di ImportGeojsonPlaces
     */
    public static function getCategories(): array
    {
        return [
            [
                'slug' => 'edukasi-budaya',
                'name' => 'Edukasi & Budaya',
                'emoji' => '🏛️',
            ],
            [
                'slug' => 'religi-sejarah',
                'name' => 'Religi & Sejarah',
                'emoji' => '🕌',
            ],
            [
                'slug' => 'wisata-air',
                'name' => 'Wisata Air',
                'emoji' => '🏖️',
            ],
            [
                'slug' => 'alam-outdoor',
                'name' => 'Alam & Outdoor',
                'emoji' => '🌳',
            ],
            [
                'slug' => 'hiburan-lifestyle',
                'name' => 'Hiburan & Lifestyle',
                'emoji' => '🛍️',
            ],
            [
                'slug' => 'aktivitas-event',
                'name' => 'Aktivitas & Event',
                'emoji' => '🚴',
            ],
        ];
    }

    /**
     * Extract kategori dari kind field place
     * Kind field berisi kategori slug langsung
     * 
     * @param string|null $kind Kind dari place
     * @return array Array of category slugs
     */
    public static function extractCategoriesFromKind(?string $kind): array
    {
        if (empty($kind) || !is_string($kind)) {
            return [];
        }

        try {
            $normalizedKind = trim($kind);

            // Ambil semua kategori slug yang valid
            $categorySlugs = array_column(self::getCategories(), 'slug');

            // Cek apakah kind adalah kategori slug yang valid (case sensitive)
            if (in_array($normalizedKind, $categorySlugs, true)) {
                return [$normalizedKind];
            }

            // Jika tidak match, return empty array
            return [];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error extracting categories from kind', [
                'kind' => $kind,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get category by slug
     */
    public static function getCategoryBySlug(string $slug): ?array
    {
        foreach (self::getCategories() as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }
        return null;
    }

    /**
     * Check if place matches any of the selected category slugs
     */
    public static function placeMatchesCategories(\App\Models\Place $place, array $categorySlugs): bool
    {
        try {
            if (empty($categorySlugs)) {
                return true; // Jika tidak ada filter, tampilkan semua
            }

            // Validasi place
            if (!$place || !$place->id) {
                return false;
            }

            // Extract categories dari kind dengan error handling
            $placeCategories = self::extractCategoriesFromKind($place->kind);

            // Jika tidak ada kategori yang ter-extract, default return true (show semua)
            // atau bisa return false jika ingin strict
            if (empty($placeCategories)) {
                // Fallback: jika tags kosong/tidak valid, kita anggap match untuk menghindari data hilang
                return true;
            }

            return !empty(array_intersect($placeCategories, $categorySlugs));
        } catch (\Throwable $e) {
            // Jika ada error, return true sebagai fallback untuk menghindari data hilang
            // Log error untuk debugging
            \Illuminate\Support\Facades\Log::warning('Error in placeMatchesCategories', [
                'place_id' => $place->id ?? null,
                'error' => $e->getMessage()
            ]);
            return true; // Fallback: show place jika ada error
        }
    }
}
