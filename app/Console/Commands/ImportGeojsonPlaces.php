<?php

namespace App\Console\Commands;

use App\Models\Place;
use App\Models\Restaurant;
use App\Models\Accommodation;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportGeojsonPlaces extends Command
{
    protected $signature = 'geojson:import {file}';
    protected $description = 'Import GeoJSON ke tabel places, restaurants, accommodations';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $alt = storage_path('app/public/' . $file);
            if (!file_exists($alt)) {
                $this->error("File tidak ditemukan: {$file}");
                return Command::FAILURE;
            }
            $file = $alt;
        }

        $this->info("📂 Membaca file: {$file}");

        $geo = json_decode(file_get_contents($file), true);
        if (!$geo || !isset($geo['features'])) {
            $this->error("File bukan GeoJSON valid.");
            return Command::FAILURE;
        }

        $features = $geo['features'];
        $this->info("📌 Features ditemukan: " . count($features));

        // COUNTER
        $cnt = [
            'places' => 0,
            'restaurants' => 0,
            'accommodations' => 0,
            'skip' => 0,
            'error' => 0,
        ];

        $bar = $this->output->createProgressBar(count($features));
        $bar->start();

        foreach ($features as $f) {
            try {

                $p = $f['properties'] ?? [];
                $g = $f['geometry'] ?? [];

                if (!$p || !$g || !isset($g['type'], $g['coordinates'])) {
                    $cnt['skip']++;
                    $bar->advance();
                    continue;
                }

                // =====================
                // COORDINATE RESOLVE
                // =====================
                $coords = $this->extractCoordinates($g);
                if (!$coords) {
                    $cnt['skip']++;
                    $bar->advance();
                    continue;
                }

                [$lon, $lat] = $coords;

                $kind = $p['kind'] ?? null;
                if (!$kind) {
                    $cnt['skip']++;
                    $bar->advance();
                    continue;
                }

                // =====================
                // MODEL ROUTING
                // =====================
                if ($kind === 'restoran') {
                    $model = Restaurant::class;
                    $cnt['restaurants']++;
                } elseif ($kind === 'penginapan') {
                    $model = Accommodation::class;
                    $cnt['accommodations']++;
                } else {
                    // Semua wisata & kategori lain
                    $model = Place::class;
                    $cnt['places']++;
                }

                // =====================
                // PAYLOAD NORMALISASI
                // =====================
                // Simpan semua properties sebagai tags untuk mapping ke categories
                $tags = array_filter($p, function ($key) {
                    return !in_array($key, ['name', 'kind', 'opening_hours', 'website_final', 'contact_final', 'facilities', 'osm_id', 'osm_type', 'element', 'id']);
                }, ARRAY_FILTER_USE_KEY);

                $payload = [
                    'name' => trim($p['name'] ?? 'Tanpa Nama'),
                    'kind' => $kind,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'opening_hours' => $p['opening_hours'] ?? null,
                    'website' => $p['website_final'] ?? null,
                    'contact' => $p['contact_final'] ?? null,
                    'facilities' => $p['facilities'] ?? null,

                    // FIELD BARU
                    'city' => $p['city_final'] ?? null,
                    'address' => $p['address_final'] ?? null,

                    'tags' => $tags,
                    'source' => 'osm',
                ];


                // =====================
                // OSM SAFE ID
                // =====================
                $osmId = $p['osm_id'] ?? null;
                $osmType = $p['osm_type'] ?? null;

                $record = null;
                if ($osmId && $osmType) {
                    $record = $model::updateOrCreate([
                        'osm_id' => $osmId,
                        'osm_type' => $osmType
                    ], $payload);
                } else {
                    // FALLBACK (jika OSM ID tidak ada)
                    $record = $model::updateOrCreate([
                        'name' => $payload['name'],
                        'latitude' => $lat,
                        'longitude' => $lon
                    ], $payload);
                }

                // =====================
                // MAP KE CATEGORIES (hanya untuk Place/wisata)
                // =====================
                if ($model === Place::class && $record) {
                    $categoryIds = $this->mapToCategories($p, $kind);
                    if (!empty($categoryIds)) {
                        $record->categories()->syncWithoutDetaching($categoryIds);
                    }
                }
            } catch (\Throwable $e) {

                Log::error("Import error", [
                    'feature' => $f,
                    'error' => $e->getMessage()
                ]);

                $cnt['error']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // =====================
        // REPORT
        // =====================
        $this->info("✅ IMPORT SELESAI");
        $this->line("━━━━━━━━━━━━━━━━━━━━");
        $this->line("Places (wisata) : " . $cnt['places']);
        $this->line("Restaurants    : " . $cnt['restaurants']);
        $this->line("Accommodations : " . $cnt['accommodations']);
        $this->line("Skipped         : " . $cnt['skip']);
        if ($cnt['error']) {
            $this->warn("Errors          : " . $cnt['error']);
        }

        $total = $cnt['places'] + $cnt['restaurants'] + $cnt['accommodations'];
        $this->info("Total berhasil import: " . $total);

        return Command::SUCCESS;
    }

    // =====================
    // GEOMETRY HANDLER
    // =====================

    private function extractCoordinates(array $geometry): ?array
    {
        $type = $geometry['type'];
        $c = $geometry['coordinates'];

        return match ($type) {
            'Point' => [$c[0], $c[1]],
            'Polygon' => $this->centroid($c[0] ?? []),
            'MultiPolygon' => $this->centroid($c[0][0] ?? []),
            'LineString' => $this->centroid($c),
            'MultiLineString' => $this->centroid($c[0] ?? []),
            default => null,
        };
    }

    private function centroid(array $coords): ?array
    {
        if (!$coords) return null;

        $sumX = 0;
        $sumY = 0;
        $n = 0;

        foreach ($coords as $pt) {
            if (isset($pt[0], $pt[1])) {
                $sumX += $pt[0];
                $sumY += $pt[1];
                $n++;
            }
        }

        if ($n === 0) return null;

        return [
            $sumX / $n,
            $sumY / $n
        ];
    }

    /**
     * Map OSM properties ke categories berdasarkan tags OSM.
     * 
     * @param array<string,mixed> $properties Properties dari GeoJSON feature
     * @param string $kind kind dari place (wisata/restoran/penginapan)
     * @return array<int> Array of category IDs
     */
    private function mapToCategories(array $properties, string $kind): array
    {
        if ($kind !== 'wisata') {
            return []; // Hanya map wisata ke categories
        }

        $categoryIds = [];

        // Ambil semua categories yang ada
        $categories = Category::all()->keyBy('slug');

        // Mapping berdasarkan tags OSM yang mungkin ada di properties
        // Kita cek berbagai kemungkinan tag OSM

        // 1. Edukasi & Budaya (museum, art_gallery, library, dll)
        $educationTags = ['museum', 'art_gallery', 'library', 'university', 'school', 'zoo'];
        if ($this->hasAnyTag($properties, $educationTags)) {
            if (isset($categories['edukasi-budaya'])) {
                $categoryIds[] = $categories['edukasi-budaya']->id;
            }
        }

        // 2. Religi & Sejarah (place_of_worship, monument, dll)
        $religionTags = ['place_of_worship', 'church', 'mosque', 'temple', 'monument', 'memorial'];
        if ($this->hasAnyTag($properties, $religionTags)) {
            if (isset($categories['religi-sejarah'])) {
                $categoryIds[] = $categories['religi-sejarah']->id;
            }
        }

        // 3. Wisata Air (beach, swimming_pool, water_park, dll)
        $waterTags = ['beach', 'swimming_pool', 'water_park', 'aquarium', 'lake', 'river'];
        if ($this->hasAnyTag($properties, $waterTags)) {
            if (isset($categories['wisata-air'])) {
                $categoryIds[] = $categories['wisata-air']->id;
            }
        }

        // 4. Alam & Outdoor (park, natural_feature, hiking_area, dll)
        $natureTags = ['park', 'natural_feature', 'hiking_area', 'mountain', 'forest', 'national_park'];
        if ($this->hasAnyTag($properties, $natureTags)) {
            if (isset($categories['alam-outdoor'])) {
                $categoryIds[] = $categories['alam-outdoor']->id;
            }
        }

        // 5. Hiburan & Lifestyle (shopping_mall, cinema, nightclub, dll)
        $entertainmentTags = ['shopping_mall', 'cinema', 'nightclub', 'casino', 'stadium'];
        if ($this->hasAnyTag($properties, $entertainmentTags)) {
            if (isset($categories['hiburan-lifestyle'])) {
                $categoryIds[] = $categories['hiburan-lifestyle']->id;
            }
        }

        // 6. Aktivitas & Event (stadium, gym, sports_centre, dll)
        $activityTags = ['stadium', 'gym', 'sports_centre', 'bowling_alley', 'amusement_park'];
        if ($this->hasAnyTag($properties, $activityTags)) {
            if (isset($categories['aktivitas-event'])) {
                $categoryIds[] = $categories['aktivitas-event']->id;
            }
        }

        // Default: jika tidak ada yang match, assign ke "Aktivitas & Event" dan "Hiburan & Lifestyle"
        if (empty($categoryIds)) {
            if (isset($categories['aktivitas-event'])) {
                $categoryIds[] = $categories['aktivitas-event']->id;
            }
            if (isset($categories['hiburan-lifestyle'])) {
                $categoryIds[] = $categories['hiburan-lifestyle']->id;
            }
        }

        return array_unique($categoryIds);
    }

    /**
     * Cek apakah properties memiliki salah satu tag dari array tags.
     * 
     * @param array<string,mixed> $properties
     * @param array<string> $tags
     * @return bool
     */
    private function hasAnyTag(array $properties, array $tags): bool
    {
        // Cek di berbagai field yang mungkin ada
        foreach ($tags as $tag) {
            // Cek di tourism tag
            if (isset($properties['tourism']) && stripos($properties['tourism'], $tag) !== false) {
                return true;
            }
            // Cek di amenity tag
            if (isset($properties['amenity']) && stripos($properties['amenity'], $tag) !== false) {
                return true;
            }
            // Cek di leisure tag
            if (isset($properties['leisure']) && stripos($properties['leisure'], $tag) !== false) {
                return true;
            }
            // Cek di historic tag
            if (isset($properties['historic']) && stripos($properties['historic'], $tag) !== false) {
                return true;
            }
            // Cek langsung di properties (case insensitive)
            foreach ($properties as $key => $value) {
                if (is_string($value) && stripos($value, $tag) !== false) {
                    return true;
                }
                if (is_string($key) && stripos($key, $tag) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
