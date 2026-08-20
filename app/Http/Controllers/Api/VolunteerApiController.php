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

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $query->where('role', 'like', "%{$request->input('role')}%");
        }

        if ($request->filled('blood_type') && $request->input('blood_type') !== 'Semua') {
            $query->where('blood_type', $request->input('blood_type'));
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->input('partner_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('certifications', 'like', "%{$search}%");
            });
        }

        // Stats calculation
        $allVolunteers = Volunteer::all();
        $stats = [
            'total_all' => $allVolunteers->count(),
            'total_pending' => $allVolunteers->whereIn('status', ['Menunggu Verifikasi', 'Pending'])->count(),
            'total_active' => $allVolunteers->where('status', 'Aktif')->count(),
            'total_inactive' => $allVolunteers->where('status', 'Tidak Aktif')->count(),
            'total_rejected' => $allVolunteers->where('status', 'Ditolak')->count(),
            'total_rescue' => $allVolunteers->filter(fn($v) => str_contains($v->role ?? '', 'Rescue'))->count(),
            'total_medis' => $allVolunteers->filter(fn($v) => str_contains($v->role ?? '', 'Medis') || str_contains($v->role ?? '', 'Dokter'))->count(),
            'total_donor' => $allVolunteers->filter(fn($v) => str_contains($v->role ?? '', 'Donor'))->count(),
        ];

        if ($request->boolean('all')) {
            $volunteers = $query->orderBy('created_at', 'desc')->get();
        } else {
            $volunteers = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
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
                'status' => 'Menunggu Verifikasi',
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
            'message' => 'Pendaftaran relawan berhasil! Data Anda masuk antrean verifikasi.',
            'data' => $volunteer
        ], 201);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'certifications' => 'nullable|string',
            'registered_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'Menunggu Verifikasi';
        }
        if (empty($validated['role'])) {
            $validated['role'] = 'Relawan Rescuer';
        }
        if (empty($validated['registered_at'])) {
            $validated['registered_at'] = now()->toDateString();
        }

        $volunteer = Volunteer::create($validated);

        if (!empty($volunteer->email)) {
            User::firstOrCreate(
                ['email' => $volunteer->email],
                [
                    'name' => $volunteer->name,
                    'password' => Hash::make('password123'),
                    'role' => 'relawan',
                    'email_verified_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Relawan berhasil ditambahkan.',
            'data' => $volunteer->load('partner')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $volunteer = Volunteer::find($id);

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Data Relawan tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'certifications' => 'nullable|string',
            'registered_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $volunteer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Relawan berhasil diperbarui.',
            'data' => $volunteer->load('partner')
        ]);
    }

    /**
     * Verifikasi & Validasi Relawan dari Flutter Mobile App (Khusus Pengurus / Admin)
     */
    public function verify(Request $request, $id)
    {
        $volunteer = Volunteer::find($id);

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Data Relawan tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Aktif,Menunggu Verifikasi,Tidak Aktif,Ditolak',
            'role' => 'nullable|string|max:100',
            'certifications' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];
        if ($request->filled('role')) {
            $updateData['role'] = $validated['role'];
        }
        if ($request->filled('certifications')) {
            $updateData['certifications'] = $validated['certifications'];
        }
        if ($request->filled('notes')) {
            $updateData['notes'] = $validated['notes'];
        }

        $volunteer->update($updateData);

        // Jika diverifikasi menjadi Aktif, pastikan akun login relawan aktif
        if ($validated['status'] === 'Aktif' && !empty($volunteer->email)) {
            User::updateOrCreate(
                ['email' => $volunteer->email],
                [
                    'name' => $volunteer->name,
                    'role' => 'relawan',
                    'email_verified_at' => now(),
                ]
            );
        }

        $statusLabel = match ($validated['status']) {
            'Aktif' => 'disetujui & diverifikasi Aktif',
            'Ditolak' => 'ditolak',
            'Menunggu Verifikasi' => 'dikembalikan ke status Menunggu Verifikasi',
            default => 'diperbarui',
        };

        return response()->json([
            'success' => true,
            'message' => "Relawan {$volunteer->name} berhasil {$statusLabel}.",
            'data' => $volunteer->load('partner')
        ]);
    }

    public function destroy($id)
    {
        $volunteer = Volunteer::find($id);

        if (!$volunteer) {
            return response()->json([
                'success' => false,
                'message' => 'Data Relawan tidak ditemukan.'
            ], 404);
        }

        $volunteer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Relawan berhasil dihapus.'
        ]);
    }
}

