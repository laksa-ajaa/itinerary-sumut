@extends('layouts.admin')

@section('title', 'Kelola Wisata (GeoJSON)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 lg:col-span-2">
        <h2 class="font-semibold mb-3">Import GeoJSON</h2>
        <form action="{{ route('admin.wisata.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".json,.geojson" required
                class="w-full border border-slate-200 rounded-lg px-3 py-2">
            <p class="text-xs text-gray-500">Gunakan struktur sama seperti perintah geojson:import.</p>
            <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Import</button>
        </form>

        @if ($importResult)
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
                <div class="p-3 rounded bg-emerald-50 border border-emerald-200">
                    <p class="text-xs text-gray-500">Places</p>
                    <p class="text-xl font-semibold">{{ $importResult['places'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded bg-emerald-50 border border-emerald-200">
                    <p class="text-xs text-gray-500">Restoran</p>
                    <p class="text-xl font-semibold">{{ $importResult['restaurants'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded bg-emerald-50 border border-emerald-200">
                    <p class="text-xs text-gray-500">Penginapan</p>
                    <p class="text-xl font-semibold">{{ $importResult['accommodations'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded bg-yellow-50 border border-yellow-200">
                    <p class="text-xs text-gray-500">Skip</p>
                    <p class="text-xl font-semibold">{{ $importResult['skip'] ?? 0 }}</p>
                </div>
                <div class="p-3 rounded bg-red-50 border border-red-200">
                    <p class="text-xs text-gray-500">Error</p>
                    <p class="text-xl font-semibold">{{ $importResult['error'] ?? 0 }}</p>
                </div>
            </div>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200">
        <h2 class="font-semibold mb-3">Tambah Manual</h2>
        <form action="{{ route('admin.wisata.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-sm text-gray-700">Nama</label>
                <input type="text" name="name" class="w-full border border-slate-200 rounded-lg px-3 py-2" required>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-sm text-gray-700">Kind</label>
                    <input type="text" name="kind" value="wisata" class="w-full border border-slate-200 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="text-sm text-gray-700">Kota</label>
                    <input type="text" name="city" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-sm text-gray-700">Latitude</label>
                    <input type="number" step="any" name="latitude" class="w-full border border-slate-200 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="text-sm text-gray-700">Longitude</label>
                    <input type="number" step="any" name="longitude" class="w-full border border-slate-200 rounded-lg px-3 py-2" required>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-700">Alamat</label>
                <input type="text" name="address" class="w-full border border-slate-200 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="text-sm text-gray-700">Deskripsi</label>
                <textarea name="description" class="w-full border border-slate-200 rounded-lg px-3 py-2" rows="2"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-sm text-gray-700">Website</label>
                    <input type="text" name="website" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="text-sm text-gray-700">Kontak</label>
                    <input type="text" name="contact" class="w-full border border-slate-200 rounded-lg px-3 py-2">
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-700">Tags (JSON)</label>
                <textarea name="tags" class="w-full border border-slate-200 rounded-lg px-3 py-2" rows="2" placeholder='{"tourism":"beach"}'></textarea>
            </div>
            <div>
                <label class="text-sm text-gray-700">Fasilitas (JSON)</label>
                <textarea name="facilities" class="w-full border border-slate-200 rounded-lg px-3 py-2" rows="2" placeholder='["toilet","wifi"]'></textarea>
            </div>
            <div>
                <label class="text-sm text-gray-700">Kategori</label>
                <select name="categories[]" multiple class="w-full border border-slate-200 rounded-lg px-3 py-2">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Tahan Ctrl / Cmd untuk memilih lebih dari satu.</p>
            </div>
            <button class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Simpan</button>
        </form>
    </div>
</div>

<div class="bg-white p-4 rounded-xl border border-slate-200">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Data Wisata</h2>
        <form action="{{ route('admin.wisata.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama..." class="border border-slate-200 rounded-lg px-3 py-2">
            <button class="px-3 py-2 border border-slate-200 rounded-lg">Cari</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-600">
                    <th class="py-2">Nama</th>
                    <th class="py-2">Kind</th>
                    <th class="py-2">Kota</th>
                    <th class="py-2">Koordinat</th>
                    <th class="py-2">Kategori</th>
                    <th class="py-2">Source</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($places as $place)
                    <tr>
                        <td class="py-2">
                            <p class="font-medium">{{ $place->name }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-xs">{{ $place->address }}</p>
                        </td>
                        <td class="py-2 capitalize">{{ $place->kind ?? '-' }}</td>
                        <td class="py-2">{{ $place->city ?? '-' }}</td>
                        <td class="py-2 text-xs text-gray-500">{{ $place->latitude }}, {{ $place->longitude }}</td>
                        <td class="py-2">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($place->categories as $cat)
                                    <span class="px-2 py-1 bg-slate-100 rounded text-xs">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-2 text-xs uppercase text-gray-500">{{ $place->source }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $places->links() }}
    </div>
</div>
@endsection

