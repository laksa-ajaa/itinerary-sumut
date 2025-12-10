<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Place;
use App\Services\GeojsonImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WisataController extends Controller
{
    public function index(Request $request)
    {
        $query = Place::with('categories')->orderByDesc('created_at');

        if ($search = $request->get('q')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $places = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('pages.admin.wisata', [
            'places' => $places,
            'categories' => $categories,
            'search' => $search,
            'importResult' => session('importResult'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'facilities' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        $tags = $this->decodeJsonField($validated['tags'] ?? null);
        $facilities = $this->decodeJsonField($validated['facilities'] ?? null);

        $place = Place::create([
            'name' => $validated['name'],
            'kind' => $validated['kind'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'website' => $validated['website'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'tags' => $tags,
            'facilities' => $facilities,
            'source' => 'manual',
        ]);

        if (!empty($validated['categories'])) {
            $place->categories()->sync($validated['categories']);
        }

        return redirect()
            ->route('admin.wisata.index')
            ->with('success', 'Destinasi berhasil ditambah.');
    }

    public function import(Request $request, GeojsonImporter $importer)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:json,geojson'],
        ]);

        $path = $request->file('file')->store('imports');
        $fullPath = storage_path('app/' . $path);

        try {
            $result = $importer->import($fullPath);

            return redirect()
                ->route('admin.wisata.index')
                ->with('success', 'Import GeoJSON selesai.')
                ->with('importResult', $result);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.wisata.index')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        } finally {
            // Hapus file upload supaya tidak menumpuk
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }

    private function decodeJsonField(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}

