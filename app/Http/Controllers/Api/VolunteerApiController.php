<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VolunteerApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Volunteer::with('partner');

        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $query->where('role', 'like', "%{$request->input('role')}%");
        }

        if ($request->filled('blood_type') && $request->input('blood_type') !== 'Semua') {
            $query->where('blood_type', $request->input('blood_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('certifications', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $volunteers
        ]);
    }

    public function show($id)
    {
        $volunteer = Volunteer::with('partner')->find($id);

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Data Relawan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $volunteer
        ]);
    }

    public function publicRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
        ]);

        $volunteer = Volunteer::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'blood_type' => $validated['blood_type'] ?? 'O',
                'role' => $validated['role'] ?? 'Relawan Rescuer',
                'status' => 'Aktif',
                'partner_id' => $validated['partner_id'] ?? null,
                'registered_at' => now()->toDateString(),
                'notes' => $validated['notes'] ?? 'Pendaftaran via Flutter Mobile App',
            ]
        );

        User::updateOrCreate(
            ['email' => $volunteer->email],
            [
                'name' => $volunteer->name,
                'email' => $volunteer->email,
                'password' => Hash::make('password123'),
                'role' => 'relawan',
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Registrasi relawan berhasil!',
            'data' => $volunteer
        ], 201);
    }
}
