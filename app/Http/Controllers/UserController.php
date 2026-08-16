<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function authorizeUserManagement()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['webmaster', 'administrator'])) {
            abort(403, 'Akses Ditolak: Hanya Webmaster dan Administrator yang dapat mengelola akun pengguna.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeUserManagement();

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $rolesList = ['administrator', 'finance', 'staff', 'webmaster', 'mitra', 'relawan', 'donatur', 'medis'];

        return Inertia::render('Users/Index', [
            'users' => $users,
            'rolesList' => $rolesList,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:administrator,finance,staff,webmaster,mitra,relawan,donatur,medis',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:administrator,finance,staff,webmaster,mitra,relawan,donatur,medis',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorizeUserManagement();

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors([
                'delete' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan.'
            ]);
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
