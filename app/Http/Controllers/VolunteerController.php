<?php

namespace App\Http\Controllers;

use App\Models\Volunteer;
use App\Models\Partner;
use App\Models\Ecosystem;
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
        $partnerQuery = Partner::with(['ecosystem'])->withCount('volunteers');

        if ($request->filled('search_partner')) {
            $s = $request->input('search_partner');
            $partnerQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('pic_name', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%")
                  ->orWhereHas('ecosystem', function ($eq) use ($s) {
                      $eq->where('name', 'like', "%{$s}%")
                         ->orWhere('region', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $partnerQuery->where('category', $request->input('category'));
        }

        if ($request->filled('ecosystem_id') && $request->input('ecosystem_id') !== 'Semua') {
            if ($request->input('ecosystem_id') === 'mandiri') {
                $partnerQuery->whereNull('ecosystem_id');
            } else {
                $partnerQuery->where('ecosystem_id', $request->input('ecosystem_id'));
            }
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

        // 3. Query Ecosystems (Ekosistem Kemanusiaan)
        $ecosystems = Ecosystem::withCount('partners')
            ->with(['partners' => function ($pq) {
                $pq->select('id', 'name', 'category', 'ecosystem_id', 'status', 'pic_name', 'address');
            }])
            ->orderBy('id', 'asc')
            ->get();

        // 4. Stats summary for Mitra & Relawan Ekosistem
        $stats = [
            'total_partners' => Partner::count(),
            'total_volunteers' => Volunteer::count(),
            'total_ecosystems' => Ecosystem::count(),
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
            'ecosystems' => $ecosystems,
            'stats' => $stats,
            'categories' => $categories,
            'roles' => $roles,
            'filters' => $request->only(['search', 'search_partner', 'category', 'role', 'status', 'blood_type', 'partner_id', 'ecosystem_id'])
        ]);
    }

    private function checkMitraReadOnly()
    {
        $user = auth()->user();
        if ($user && $user->role === 'mitra') {
            abort(403, 'Akses Ditolak: Akun mitra hanya memiliki izin melihat data (read-only) pada direktori ini. Silakan kelola personel lembaga Anda melalui menu Pengurus & Anggota.');
        }
    }

    // --- ECOSYSTEM (EKOSISTEM KEMANUSIAAN) CRUD ---
    public function storeEcosystem(Request $request)
    {
        $this->checkMitraReadOnly();

        $messages = [
            'name.required' => 'Nama Ekosistem wajib diisi.',
            'name.max' => 'Nama Ekosistem maksimal 255 karakter.',
            'code.unique' => 'Kode atau singkatan ekosistem ":input" sudah terdaftar. Silakan gunakan kode lain atau kosongkan untuk otomatis.',
            'code.max' => 'Kode ekosistem maksimal 50 karakter.',
            'region.max' => 'Wilayah maksimal 255 karakter.',
            'lead_institution.max' => 'Lembaga pemrakarsa maksimal 255 karakter.',
            'pic_name.max' => 'Nama PIC maksimal 255 karakter.',
            'pic_phone.max' => 'Nomor telepon PIC maksimal 50 karakter.',
            'pic_email.email' => 'Format email PIC tidak valid.',
            'status.required' => 'Status ekosistem wajib dipilih.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:ecosystems,code',
            'region' => 'nullable|string|max:255',
            'lead_institution' => 'nullable|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:50',
        ], $messages);

        if (empty($validated['code'])) {
            $maxId = (Ecosystem::max('id') ?? 0) + 1;
            do {
                $candidate = 'EKO-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);
                $exists = Ecosystem::where('code', $candidate)->exists();
                $maxId++;
            } while ($exists);
            $validated['code'] = $candidate;
        } else {
            $validated['code'] = strtoupper(trim($validated['code']));
        }

        Ecosystem::create($validated);

        return redirect()->back()->with('success', 'Ekosistem Kemanusiaan berhasil ditambahkan.');
    }

    public function updateEcosystem(Request $request, Ecosystem $ecosystem)
    {
        $this->checkMitraReadOnly();

        $messages = [
            'name.required' => 'Nama Ekosistem wajib diisi.',
            'name.max' => 'Nama Ekosistem maksimal 255 karakter.',
            'code.unique' => 'Kode atau singkatan ekosistem ":input" sudah terdaftar pada ekosistem lain.',
            'code.max' => 'Kode ekosistem maksimal 50 karakter.',
            'region.max' => 'Wilayah maksimal 255 karakter.',
            'lead_institution.max' => 'Lembaga pemrakarsa maksimal 255 karakter.',
            'pic_name.max' => 'Nama PIC maksimal 255 karakter.',
            'pic_phone.max' => 'Nomor telepon PIC maksimal 50 karakter.',
            'pic_email.email' => 'Format email PIC tidak valid.',
            'status.required' => 'Status ekosistem wajib dipilih.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:ecosystems,code,' . $ecosystem->id,
            'region' => 'nullable|string|max:255',
            'lead_institution' => 'nullable|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:50',
        ], $messages);

        if (!empty($validated['code'])) {
            $validated['code'] = strtoupper(trim($validated['code']));
        }

        $ecosystem->update($validated);

        return redirect()->back()->with('success', 'Data Ekosistem berhasil diperbarui.');
    }

    public function destroyEcosystem(Ecosystem $ecosystem)
    {
        $this->checkMitraReadOnly();

        $ecosystem->delete();

        return redirect()->back()->with('success', 'Ekosistem berhasil dihapus.');
    }

    // --- PARTNER (MITRA) CRUD ---
    public function storePartner(Request $request)
    {
        $this->checkMitraReadOnly();

        $validated = $request->validate([
            'ecosystem_id' => 'nullable|exists:ecosystems,id',
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
        $this->checkMitraReadOnly();

        $validated = $request->validate([
            'ecosystem_id' => 'nullable|exists:ecosystems,id',
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
        $this->checkMitraReadOnly();

        $partner->delete();
        return redirect()->back()->with('success', 'Profil Mitra berhasil dihapus.');
    }

    // --- VOLUNTEER (RELAWAN) CRUD ---
    public function store(Request $request)
    {
        $this->checkMitraReadOnly();
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
        if ($request->input('type') === 'mitra' || $request->has('category') || $request->input('role') === 'Mitra Lembaga') {
            return $this->publicRegisterPartner($request);
        }

        $membershipType = $request->input('membership_type', 'relawan');

        $validated = $request->validate([
            'partner_id' => 'nullable|exists:partners,id',
            'membership_type' => 'nullable|in:anggota,relawan',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'password' => 'nullable|string|min:6',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms_accepted' => 'nullable|boolean',
        ], [
            'terms_accepted.accepted' => 'Anda wajib menyetujui Syarat & Ketentuan Keanggotaan untuk mendaftar sebagai Anggota Lembaga.',
        ]);

        if ($membershipType === 'anggota' && !$request->boolean('terms_accepted')) {
            return back()->withErrors([
                'terms_accepted' => 'Anda wajib menyetujui Syarat & Ketentuan Keanggotaan untuk mendaftar sebagai Anggota Lembaga.',
            ]);
        }

        $validated['membership_type'] = $membershipType;
        unset($validated['terms_accepted']);

        $defaultRole = ($membershipType === 'anggota') ? 'Anggota Personel Lembaga' : 'Relawan Rescuer';
        $validated['role'] = $validated['role'] ?? $defaultRole;
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
                'partner_id' => $volunteer->partner_id,
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

        $successMsg = ($membershipType === 'anggota')
            ? 'Pendaftaran berhasil! Formulir pendaftaran Anggota Resmi Lembaga telah diterima.'
            : 'Pendaftaran berhasil! Akun Relawan Kemanusiaan telah dibuat.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'volunteer' => $volunteer,
                'email_sent' => $emailSent
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }

    public function publicRegisterPartner(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string',
            'mou_number' => 'nullable|string|max:100',
            'personnel_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'terms_accepted' => 'required|accepted',
        ], [
            'terms_accepted.required' => 'Anda wajib menyetujui Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia.',
            'terms_accepted.accepted' => 'Anda wajib menyetujui Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia.',
        ]);

        $prefix = match ($validated['category']) {
            'PMI' => 'MTR-PMI-',
            'Basarnas' => 'MTR-BAS-',
            'BPBD' => 'MTR-BPBD-',
            'Rumah Sakit' => 'MTR-RS-',
            'Tim Rescue' => 'MTR-RSC-',
            'Filantropi' => 'MTR-FLT-',
            'CSR Swasta', 'CSR' => 'MTR-CSR-',
            default => 'MTR-GEN-',
        };
        $nextId = Partner::count() + 1;
        $code = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $passwordInput = $validated['password'] ?? 'password123';
        unset($validated['password'], $validated['terms_accepted']);

        $partner = Partner::create(array_merge($validated, [
            'code' => $code,
            'status' => 'Aktif',
            'personnel_count' => (int) ($validated['personnel_count'] ?? 0),
        ]));

        \App\Models\User::updateOrCreate(
            ['email' => $partner->email],
            [
                'name' => $partner->name . ($partner->pic_name ? ' (' . $partner->pic_name . ')' : ''),
                'email' => $partner->email,
                'password' => \Illuminate\Support\Facades\Hash::make($passwordInput),
                'role' => 'mitra',
                'partner_id' => $partner->id,
                'email_verified_at' => now(),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrasi Kemitraan & Kolaborasi MKT berhasil! Profil mitra dan akun sistem telah dibuat.',
                'partner' => $partner,
            ]);
        }

        return redirect()->back()->with('success', 'Registrasi Kemitraan & Kolaborasi MKT berhasil!');
    }

    public function update(Request $request, Volunteer $volunteer)
    {
        $this->checkMitraReadOnly();

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
        $this->checkMitraReadOnly();

        $volunteer->delete();
        return redirect()->back()->with('success', 'Data Relawan berhasil dihapus.');
    }
}
