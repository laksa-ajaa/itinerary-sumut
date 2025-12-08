<?php

namespace App\Console\Commands;

use App\Models\Place;
use App\Models\Category;
use Illuminate\Console\Command;

class AssignCategoriesToPlaces extends Command
{
    protected $signature = 'places:assign-categories';
    protected $description = 'Assign categories ke places yang sudah diimport berdasarkan tags OSM';

    public function handle(): int
    {
        $this->info('🔄 Memulai assign categories ke places...');
        
        $places = Place::where('kind', 'wisata')
            ->whereNotNull('tags')
            ->get();
        
        $this->info("📌 Ditemukan {$places->count()} places untuk di-assign categories");
        
        $bar = $this->output->createProgressBar($places->count());
        $bar->start();
        
        $categories = Category::all()->keyBy('slug');
        $assigned = 0;
        
        foreach ($places as $place) {
            $tags = $place->tags ?? [];
            $categoryIds = $this->mapToCategories($tags, 'wisata', $categories);
            
            if (!empty($categoryIds)) {
                $place->categories()->syncWithoutDetaching($categoryIds);
                $assigned++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Selesai! {$assigned} places berhasil di-assign categories");
        
        return Command::SUCCESS;
    }

    /**
     * Map OSM tags ke categories.
     */
    private function mapToCategories(array $tags, string $kind, $categories): array
    {
        if ($kind !== 'wisata') {
            return [];
        }

        $categoryIds = [];
        
        // 1. Edukasi & Budaya
        $educationTags = ['museum', 'art_gallery', 'library', 'university', 'school', 'zoo'];
        if ($this->hasAnyTag($tags, $educationTags)) {
            if (isset($categories['edukasi-budaya'])) {
                $categoryIds[] = $categories['edukasi-budaya']->id;
            }
        }
        
        // 2. Religi & Sejarah
        $religionTags = ['place_of_worship', 'church', 'mosque', 'temple', 'monument', 'memorial'];
        if ($this->hasAnyTag($tags, $religionTags)) {
            if (isset($categories['religi-sejarah'])) {
                $categoryIds[] = $categories['religi-sejarah']->id;
            }
        }
        
        // 3. Wisata Air
        $waterTags = ['beach', 'swimming_pool', 'water_park', 'aquarium', 'lake', 'river'];
        if ($this->hasAnyTag($tags, $waterTags)) {
            if (isset($categories['wisata-air'])) {
                $categoryIds[] = $categories['wisata-air']->id;
            }
        }
        
        // 4. Alam & Outdoor
        $natureTags = ['park', 'natural_feature', 'hiking_area', 'mountain', 'forest', 'national_park'];
        if ($this->hasAnyTag($tags, $natureTags)) {
            if (isset($categories['alam-outdoor'])) {
                $categoryIds[] = $categories['alam-outdoor']->id;
            }
        }
        
        // 5. Hiburan & Lifestyle
        $entertainmentTags = ['shopping_mall', 'cinema', 'nightclub', 'casino', 'stadium'];
        if ($this->hasAnyTag($tags, $entertainmentTags)) {
            if (isset($categories['hiburan-lifestyle'])) {
                $categoryIds[] = $categories['hiburan-lifestyle']->id;
            }
        }
        
        // 6. Aktivitas & Event
        $activityTags = ['stadium', 'gym', 'sports_centre', 'bowling_alley', 'amusement_park'];
        if ($this->hasAnyTag($tags, $activityTags)) {
            if (isset($categories['aktivitas-event'])) {
                $categoryIds[] = $categories['aktivitas-event']->id;
            }
        }
        
        // Default
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

    private function hasAnyTag(array $tags, array $searchTags): bool
    {
        foreach ($searchTags as $tag) {
            foreach ($tags as $key => $value) {
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




