<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('created_at')->paginate(20);

        return view('pages.admin.users', [
            'users' => $users,
        ]);
    }

    public function makeAdmin(User $user)
    {
        $user->update(['is_admin' => true]);

        return back()->with('success', "{$user->name} sekarang menjadi admin.");
    }

    public function revokeAdmin(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa mencabut akses admin diri sendiri.');
        }

        $user->update(['is_admin' => false]);

        return back()->with('success', "Akses admin untuk {$user->name} dicabut.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', "Akun {$user->name} berhasil dihapus.");
    }
}

