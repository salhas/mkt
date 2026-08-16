<?php

namespace App\Http\Controllers;

use App\Models\Volunteer;
use App\Models\Partner;
use App\Mail\VolunteerRegisteredMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Partners (Mitra Lembaga)
        $partnerQuery = Partner::withCount('volunteers');

        if ($request->filled('search_partner')) {
            $s = $request->input('search_partner');
            $partnerQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('pic_name', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $partnerQuery->where('category', $request->input('category'));
        }

        $partners = $partnerQuery->orderBy('id', 'asc')->get();

        // 2. Query Volunteers (Anggota & Relawan Personel)
        $volunteerQuery = Volunteer::with('partner');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $volunteerQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('certifications', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $volunteerQuery->where('role', $request->input('role'));
        }

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $volunteerQuery->where('status', $request->input('status'));
        }

        if ($request->filled('blood_type') && $request->input('blood_type') !== 'Semua') {
            $volunteerQuery->where('blood_type', $request->input('blood_type'));
        }

        if ($request->filled('partner_id')) {
            $volunteerQuery->where('partner_id', $request->input('partner_id'));
        }

        $volunteers = $volunteerQuery->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // 3. Stats summary for Mitra & Relawan Ekosistem
        $stats = [
            'total_partners' => Partner::count(),
            'total_volunteers' => Volunteer::count(),
            'total_rescue' => Volunteer::where('role', 'like', '%Rescue%')->count(),
            'total_medis' => Volunteer::where('role', 'like', '%Medis%')->orWhere('role', 'like', '%Dokter%')->count(),
            'total_donor' => Volunteer::where('role', 'like', '%Donor%')->count(),
            'total_basarnas_bpbd' => Partner::whereIn('category', ['Basarnas', 'BPBD'])->sum('personnel_count'),
        ];

        $categories = ['Semua', 'PMI', 'Basarnas', 'BPBD', 'Rumah Sakit', 'Tim Rescue', 'Filantropi'];
        $roles = ['Semua', 'Tim Rescue', 'Relawan Rescuer', 'Tenaga Medis', 'Donor Darah', 'Relawan Logistik', 'Staff Basarnas/BPBD', 'Relawan Umum'];

        return Inertia::render('Volunteers/Index', [
            'partners' => $partners,
            'volunteers' => $volunteers,
            'stats' => $stats,
            'categories' => $categories,
            'roles' => $roles,
            'filters' => $request->only(['search', 'search_partner', 'category', 'role', 'status', 'blood_type', 'partner_id'])
        ]);
    }

    // --- PARTNER (MITRA) CRUD ---
    public function storePartner(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:100|unique:partners,code',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|string|max:50',
            'mou_number' => 'nullable|string|max:100',
            'personnel_count' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $prefix = match ($validated['category']) {
                'PMI' => 'MTR-PMI-',
                'Basarnas' => 'MTR-BAS-',
                'BPBD' => 'MTR-BPBD-',
                'Rumah Sakit' => 'MTR-RS-',
                'Tim Rescue' => 'MTR-RSC-',
                default => 'MTR-GEN-',
            };
            $nextId = Partner::count() + 1;
            $validated['code'] = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        Partner::create($validated);

        return redirect()->back()->with('success', 'Profil Mitra Lembaga berhasil ditambahkan.');
    }

    public function updatePartner(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:100|unique:partners,code,' . $partner->id,
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|string|max:50',
            'mou_number' => 'nullable|string|max:100',
            'personnel_count' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $partner->update($validated);

        return redirect()->back()->with('success', 'Data Profil Mitra berhasil diperbarui.');
    }

    public function destroyPartner(Partner $partner)
    {
        $partner->delete();
        return redirect()->back()->with('success', 'Profil Mitra berhasil dihapus.');
    }

    // --- VOLUNTEER (RELAWAN) CRUD ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:volunteers,email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'certifications' => 'nullable|string',
            'registered_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['registered_at'])) {
            $validated['registered_at'] = now()->toDateString();
        }

        $volunteer = Volunteer::create($validated);

        if (!empty($volunteer->email)) {
            try {
                Mail::to($volunteer->email)->send(new VolunteerRegisteredMail($volunteer));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email relawan: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Relawan / Anggota Mitra berhasil ditambahkan.');
    }

    public function publicRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'password' => 'nullable|string|min:6',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['role'] = $validated['role'] ?? 'Relawan Rescuer';
        $validated['status'] = 'Aktif';
        $validated['registered_at'] = now()->toDateString();

        $passwordInput = $request->input('password', 'password123');
        unset($validated['password']);

        $volunteer = Volunteer::updateOrCreate(
            ['email' => $validated['email']],
            $validated
        );

        \App\Models\User::updateOrCreate(
            ['email' => $volunteer->email],
            [
                'name' => $volunteer->name,
                'email' => $volunteer->email,
                'password' => \Illuminate\Support\Facades\Hash::make($passwordInput),
                'role' => 'relawan',
                'email_verified_at' => now(),
            ]
        );

        $emailSent = false;
        try {
            Mail::to($volunteer->email)->send(new VolunteerRegisteredMail($volunteer));
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email konfirmasi registrasi relawan: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil! Akun relawan telah dibuat.',
                'volunteer' => $volunteer,
                'email_sent' => $emailSent
            ]);
        }

        return redirect()->back()->with('success', 'Pendaftaran berhasil! Akun relawan telah dibuat.');
    }

    public function update(Request $request, Volunteer $volunteer)
    {
        $validated = $request->validate([
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:volunteers,email,' . $volunteer->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'certifications' => 'nullable|string',
            'registered_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $volunteer->update($validated);

        return redirect()->back()->with('success', 'Data Relawan / Anggota berhasil diperbarui.');
    }

    public function destroy(Volunteer $volunteer)
    {
        $volunteer->delete();
        return redirect()->back()->with('success', 'Data Relawan berhasil dihapus.');
    }
}
