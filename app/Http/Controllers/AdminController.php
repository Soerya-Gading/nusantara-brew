<?php

namespace App\Http\Controllers;

use App\Models\CoffeeShop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    private function adminNav(): array
    {
        return [
            ['label' => 'Ringkasan platform', 'route' => 'admin.dashboard'],
            ['label' => 'Persetujuan kedai', 'route' => 'admin.shops'],
            ['label' => 'Manajemen pengguna', 'route' => 'admin.users'],
        ];
    }

    public function dashboard()
    {
        $user = auth()->user();

        $activeShops = CoffeeShop::where('status', 'active')->count();
        $pendingShops = CoffeeShop::where('status', 'pending')->count();
        $totalUsers = User::count();

        $pending = CoffeeShop::where('status', 'pending')->latest()->get();

        return view('admin.dashboard', [
            'summary' => [
                'active_shops' => $activeShops,
                'pending_shops' => $pendingShops,
                'users' => $totalUsers,
            ],
            'pending' => $pending,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->adminNav(),
        ]);
    }

    public function shops()
    {
        $user = auth()->user();
        $shops = CoffeeShop::with('owner')->latest()->get();

        return view('admin.shops', [
            'shops' => $shops,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->adminNav(),
        ]);
    }

    public function updateShopStatus(CoffeeShop $coffeeShop, Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,inactive'],
        ]);

        $coffeeShop->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status kedai kopi berhasil diperbarui.');
    }

    public function users()
    {
        $user = auth()->user();
        $users = User::latest()->get();

        return view('admin.users', [
            'users' => $users,
            'account' => (object) [
                'name' => $user->name,
                'role_label' => $user->role_label,
            ],
            'nav' => $this->adminNav(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:owner,staff'],
            'password' => ['nullable', 'string', Password::min(6)],
        ]);

        $password = $validated['password'] ?? 'password123';

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users')->with('success', 'Akun berhasil dibuat.');
    }

    public function updateUserStatus(User $user, Request $request)
    {
        $user->is_active = $request->has('is_active') ? $request->boolean('is_active') : !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', 'Status akun pengguna berhasil diperbarui.');
    }
}
