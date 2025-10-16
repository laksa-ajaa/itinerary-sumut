<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Place;
use App\Models\Category;

class ImportPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'places:import {lat} {lng} {--radius=3000} {--type=tourist_attraction} {--language=id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data tempat dari Google Places Nearby Search + Details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lat = (string)$this->argument('lat');
        $lng = (string)$this->argument('lng');
        $radius = (int)$this->option('radius');
        $type = (string)$this->option('type');
        $language = (string)$this->option('language');

        $key = config('services.google.places_key');
        if (!$key) {
            $this->error('GOOGLE_PLACES_KEY belum di-set.');
            return self::FAILURE;
        }

        $params = [
            'key' => $key,
            'location' => "$lat,$lng",
            'radius' => $radius,
            'type' => $type,
            'language' => $language,
        ];

        $token = null;
        $count = 0;
        do {
            $resp = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', array_filter([
                ...$params,
                'pagetoken' => $token,
            ]));
            $json = $resp->json();
            $results = $json['results'] ?? [];
            foreach ($results as $r) {
                $placeId = $r['place_id'];
                $name = $r['name'] ?? null;
                $location = $r['geometry']['location'] ?? null;
                if (!$name || !$location) continue;

                $exists = Place::where('google_place_id', $placeId)->first();
                if ($exists) continue;

                $details = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'key' => $key,
                    'place_id' => $placeId,
                    'language' => $language,
                    'fields' => 'place_id,name,geometry,opening_hours,price_level,rating,user_ratings_total,formatted_address,types'
                ])->json('result') ?? [];

                # Tentukan kind sederhana dari type/jenis Google
                $kind = null
                if ($type):
                    $kind = match ($type) {
                        'restaurant', 'cafe', 'meal_takeaway' => 'restaurant',
                        'lodging' => 'lodging',
                        'tourist_attraction', 'museum', 'park' => 'attraction',
                        default => $type,
                    };
                else:
                    $typesList = collect($r['types'] ?? []);
                    if ($typesList->contains('restaurant') or $typesList->contains('cafe')):
                        $kind = 'restaurant';
                    elif $typesList->contains('lodging'):
                        $kind = 'lodging';
                    elif $typesList->contains('tourist_attraction') or $typesList->contains('museum') or $typesList->contains('park'):
                        $kind = 'attraction';
                    else:
                        $kind = $typesList->first();
                    endif;
                endif;

                $p = Place::create([
                    'name' => $name,
                    'kind' => $kind,
                    'description' => $details['formatted_address'] ?? null,
                    'open_time' => null,
                    'close_time' => null,
                    'entry_price' => null,
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'provinces' => null,
                    'city' => null,
                    'subdistrict' => null,
                    'street_name' => null,
                    'postal_code' => null,
                    'google_place_id' => $placeId,
                    'rating_avg' => (float)($details['rating'] ?? 0),
                    'rating_count' => (int)($details['user_ratings_total'] ?? 0),
                ]);

                $types = collect($details['types'] ?? []);
                foreach ($types as $t) {
                    $cat = Category::firstOrCreate(['name' => $t]);
                    $p->categories()->syncWithoutDetaching([$cat->id]);
                }

                $count++;
            }

            $token = $json['next_page_token'] ?? null;
            if ($token) usleep(2500000); // 2.5s delay sesuai ketentuan
        } while ($token);

        $this->info("Imported: $count places");
        return self::SUCCESS;
    }
}
