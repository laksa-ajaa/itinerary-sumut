<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->insert([
            [
                'slug' => 'alam-outdoor',
                'name' => 'Alam & Outdoor',
                'emoji' => '🌳',
                // 'description' => 'Destinasi berbasis alam dan kegiatan luar ruangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'wisata-air',
                'name' => 'Wisata Air',
                'emoji' => '🏖️',
                // 'description' => 'Atraksi berbasis air dan rekreasi akuatik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'edukasi-budaya',
                'name' => 'Edukasi & Budaya',
                'emoji' => '🏛️',
                // 'description' => 'Tempat edukasi, seni, dan kebudayaan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'religi-sejarah',
                'name' => 'Religi & Sejarah',
                'emoji' => '🕌',
                // 'description' => 'Lokasi religi dan bersejarah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'hiburan-lifestyle',
                'name' => 'Hiburan & Lifestyle',
                'emoji' => '🛍️',
                // 'description' => 'Pusat hiburan, belanja, dan gaya hidup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'aktivitas-event',
                'name' => 'Aktivitas & Event',
                'emoji' => '🚴',
                // 'description' => 'Kegiatan olahraga, event, dan aktivitas khusus',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
