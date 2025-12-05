<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Place;
use App\Models\UserRating;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kategori untuk preferensi
        $alam = Category::where('name', 'Alam')->first();
        $kuliner = Category::where('name', 'Kuliner')->first();
        $sejarah = Category::where('name', 'Sejarah')->first();
        
        // User 1: Suka Alam, Kuliner, Sejarah
        $user1 = User::create([
            'name' => 'Ahmad Rizki',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password'),
        ]);
        
        // Beri rating tinggi ke tempat-tempat dengan kategori yang disukai
        $places = Place::with('categories')->get();
        foreach ($places as $place) {
            $placeCategories = $place->categories->pluck('id')->toArray();
            $rating = 3; // default
            
            if (in_array($alam->id, $placeCategories) || 
                in_array($kuliner->id, $placeCategories) || 
                in_array($sejarah->id, $placeCategories)) {
                $rating = 5; // sangat suka
            }
            
            UserRating::create([
                'user_id' => $user1->id,
                'place_id' => $place->id,
                'rating' => $rating,
            ]);
        }
        
        // User 2: Suka Kuliner dan Budaya
        $budaya = Category::where('name', 'Budaya')->first();
        $user2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
        ]);
        
        foreach ($places as $place) {
            $placeCategories = $place->categories->pluck('id')->toArray();
            $rating = 3;
            
            if (in_array($kuliner->id, $placeCategories) || 
                in_array($budaya->id, $placeCategories)) {
                $rating = 5;
            }
            
            UserRating::create([
                'user_id' => $user2->id,
                'place_id' => $place->id,
                'rating' => $rating,
            ]);
        }
        
        // User 3: Suka Alam dan Pantai
        $pantai = Category::where('name', 'Pantai')->first();
        $user3 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);
        
        foreach ($places as $place) {
            $placeCategories = $place->categories->pluck('id')->toArray();
            $rating = 3;
            
            if (in_array($alam->id, $placeCategories) || 
                in_array($pantai->id, $placeCategories)) {
                $rating = 5;
            }
            
            UserRating::create([
                'user_id' => $user3->id,
                'place_id' => $place->id,
                'rating' => $rating,
            ]);
        }
    }
}






