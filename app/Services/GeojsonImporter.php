<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Category;
use App\Models\Place;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Log;

class GeojsonImporter
{
    /**
     * Import GeoJSON features into corresponding models.
     *
     * @param  string         $filePath
     * @param  callable|null  $onProgress fn(int $done, int $total, array $counts)
     * @return array<string,int>
     */
    public function import(string $filePath, ?callable $onProgress = null): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$filePath}");
        }

        $geo = json_decode(file_get_contents($filePath), true);
        if (!$geo || !isset($geo['features'])) {
            throw new \InvalidArgumentException('File bukan GeoJSON valid.');
        }

        $features = $geo['features'];

        $counts = [
            'places' => 0,
            'restaurants' => 0,
            'accommodations' => 0,
            'skip' => 0,
            'error' => 0,
            'features' => count($features),
            'imported' => 0,
        ];

        foreach ($features as $idx => $feature) {
            try {
                $properties = $feature['properties'] ?? [];
                $geometry = $feature['geometry'] ?? [];

                if (!$properties || !$geometry || !isset($geometry['type'], $geometry['coordinates'])) {
                    $counts['skip']++;
                    continue;
                }

                $coords = $this->extractCoordinates($geometry);
                if (!$coords) {
                    $counts['skip']++;
                    continue;
                }

                [$lon, $lat] = $coords;
                $kind = $properties['kind'] ?? null;

                if (!$kind) {
                    $counts['skip']++;
                    continue;
                }

                if ($kind === 'restoran') {
                    $model = Restaurant::class;
                    $counts['restaurants']++;
                } elseif ($kind === 'penginapan') {
                    $model = Accommodation::class;
                    $counts['accommodations']++;
                } else {
                    $model = Place::class;
                    $counts['places']++;
                }

                $tags = array_filter($properties, function ($key) {
                    return !in_array($key, [
                        'name',
                        'kind',
                        'opening_hours',
                        'website_final',
                        'contact_final',
                        'facilities',
                        'osm_id',
                        'osm_type',
                        'element',
                        'id',
                    ]);
                }, ARRAY_FILTER_USE_KEY);

                $payload = [
                    'name' => trim($properties['name'] ?? 'Tanpa Nama'),
                    'kind' => $kind,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'opening_hours' => $properties['opening_hours'] ?? null,
                    'website' => $properties['website_final'] ?? null,
                    'contact' => $properties['contact_final'] ?? null,
                    'facilities' => $properties['facilities'] ?? null,
                    'city' => $properties['city_final'] ?? null,
                    'address' => $properties['address_final'] ?? null,
                    'tags' => $tags,
                    'source' => 'osm',
                ];

                $osmId = $properties['osm_id'] ?? null;
                $osmType = $properties['osm_type'] ?? null;

                if ($osmId && $osmType) {
                    $record = $model::updateOrCreate([
                        'osm_id' => $osmId,
                        'osm_type' => $osmType
                    ], $payload);
                } else {
                    $record = $model::updateOrCreate([
                        'name' => $payload['name'],
                        'latitude' => $lat,
                        'longitude' => $lon
                    ], $payload);
                }

                if ($model === Place::class && $record) {
                    $categoryIds = $this->mapToCategories($properties, $kind);
                    if (!empty($categoryIds)) {
                        $record->categories()->syncWithoutDetaching($categoryIds);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Import error', [
                    'feature' => $feature,
                    'error' => $e->getMessage(),
                ]);
                $counts['error']++;
            } finally {
                if ($onProgress) {
                    $onProgress($idx + 1, $counts['features'], $counts);
                }
            }
        }

        $counts['imported'] = $counts['places'] + $counts['restaurants'] + $counts['accommodations'];

        return $counts;
    }

    private function extractCoordinates(array $geometry): ?array
    {
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? null;

        return match ($type) {
            'Point' => [$coords[0], $coords[1]],
            'Polygon' => $this->centroid($coords[0] ?? []),
            'MultiPolygon' => $this->centroid($coords[0][0] ?? []),
            'LineString' => $this->centroid($coords),
            'MultiLineString' => $this->centroid($coords[0] ?? []),
            default => null,
        };
    }

    private function centroid(array $coords): ?array
    {
        if (!$coords) {
            return null;
        }

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

        if ($n === 0) {
            return null;
        }

        return [
            $sumX / $n,
            $sumY / $n,
        ];
    }

    /**
     * Map OSM properties ke categories berdasarkan tags OSM.
     *
     * @param array<string,mixed> $properties
     * @param string              $kind
     * @return array<int>
     */
    private function mapToCategories(array $properties, string $kind): array
    {
        if ($kind !== 'wisata') {
            return [];
        }

        $categoryIds = [];
        $categories = Category::all()->keyBy('slug');

        $educationTags = ['museum', 'art_gallery', 'library', 'university', 'school', 'zoo'];
        if ($this->hasAnyTag($properties, $educationTags) && isset($categories['edukasi-budaya'])) {
            $categoryIds[] = $categories['edukasi-budaya']->id;
        }

        $religionTags = ['place_of_worship', 'church', 'mosque', 'temple', 'monument', 'memorial'];
        if ($this->hasAnyTag($properties, $religionTags) && isset($categories['religi-sejarah'])) {
            $categoryIds[] = $categories['religi-sejarah']->id;
        }

        $waterTags = ['beach', 'swimming_pool', 'water_park', 'aquarium', 'lake', 'river'];
        if ($this->hasAnyTag($properties, $waterTags) && isset($categories['wisata-air'])) {
            $categoryIds[] = $categories['wisata-air']->id;
        }

        $natureTags = ['park', 'natural_feature', 'hiking_area', 'mountain', 'forest', 'national_park'];
        if ($this->hasAnyTag($properties, $natureTags) && isset($categories['alam-outdoor'])) {
            $categoryIds[] = $categories['alam-outdoor']->id;
        }

        $entertainmentTags = ['shopping_mall', 'cinema', 'nightclub', 'casino', 'stadium'];
        if ($this->hasAnyTag($properties, $entertainmentTags) && isset($categories['hiburan-lifestyle'])) {
            $categoryIds[] = $categories['hiburan-lifestyle']->id;
        }

        if (empty($categoryIds) && isset($categories['hiburan-lifestyle'])) {
            $categoryIds[] = $categories['hiburan-lifestyle']->id;
        }

        return array_unique($categoryIds);
    }

    /**
     * Cek apakah properties memiliki salah satu tag dari array tags.
     *
     * @param array<string,mixed> $properties
     * @param array<string>       $tags
     */
    private function hasAnyTag(array $properties, array $tags): bool
    {
        foreach ($tags as $tag) {
            if (isset($properties['tourism']) && stripos((string) $properties['tourism'], $tag) !== false) {
                return true;
            }
            if (isset($properties['amenity']) && stripos((string) $properties['amenity'], $tag) !== false) {
                return true;
            }
            if (isset($properties['leisure']) && stripos((string) $properties['leisure'], $tag) !== false) {
                return true;
            }
            if (isset($properties['historic']) && stripos((string) $properties['historic'], $tag) !== false) {
                return true;
            }
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

