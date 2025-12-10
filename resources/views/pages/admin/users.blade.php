@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="bg-white p-4 rounded-xl border border-slate-200">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Daftar User</h2>
        <p class="text-sm text-gray-500">Kelola role admin dan akun</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-gray-600">
                    <th class="py-2">Nama</th>
                    <th class="py-2">Email</th>
                    <th class="py-2">Admin</th>
                    <th class="py-2">Bergabung</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="py-2 font-medium">{{ $user->name }}</td>
                        <td class="py-2">{{ $user->email }}</td>
                        <td class="py-2">
                            @if ($user->is_admin)
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs">Admin</span>
                            @else
                                <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs">User</span>
                            @endif
                        </td>
                        <td class="py-2 text-xs text-gray-500">{{ $user->created_at?->format('d M Y') }}</td>
                        <td class="py-2 flex items-center gap-2">
                            @if (!$user->is_admin)
                                <form action="{{ route('admin.users.make-admin', $user) }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1 border border-slate-200 rounded-lg text-xs">Jadikan Admin</button>
                                </form>
                            @else
                                <form action="{{ route('admin.users.revoke-admin', $user) }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1 border border-red-200 text-red-600 rounded-lg text-xs">Cabut Admin</button>
                                </form>
                            @endif
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 border border-red-200 text-red-600 rounded-lg text-xs">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection

