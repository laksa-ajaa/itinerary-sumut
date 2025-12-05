<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menghapus duplikat berdasarkan nama
        // Menyisakan satu record per nama (yang paling lama dibuat)
        $duplicates = DB::table('places')
            ->select('name', DB::raw('COUNT(*) as total'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Ambil ID yang akan dipertahankan (yang paling lama dibuat)
            $keepId = DB::table('places')
                ->where('name', $duplicate->name)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->value('id');

            if ($keepId) {
                // Hapus semua record dengan nama yang sama kecuali yang dipertahankan
                DB::table('places')
                    ->where('name', $duplicate->name)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak bisa di-reverse karena data sudah dihapus
        // Migration ini tidak bisa di-rollback
    }
};
