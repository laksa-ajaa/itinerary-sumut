<?php

namespace App\Console\Commands;

use App\Services\GeojsonImporter;
use Illuminate\Console\Command;

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

        $bar = $this->output->createProgressBar(count($features));
        $bar->start();

        $importer = app(GeojsonImporter::class);
        $cnt = $importer->import($file, function () use ($bar) {
            $bar->advance();
        });

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

        $this->info("Total berhasil import: " . $cnt['imported']);

        return Command::SUCCESS;
    }
}
