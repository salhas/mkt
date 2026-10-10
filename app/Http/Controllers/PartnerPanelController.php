<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Partner;
use App\Models\PartnerEquipment;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PartnerPanelController extends Controller
{
    /**
     * Tampilkan Halaman Profil Lembaga Mitra
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra belum terdaftar.');
        }

        return Inertia::render('Partner/Profile', [
            'partner' => $partner,
        ]);
    }

    /**
     * Perbarui Data Profil Lembaga Mitra
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:partners,slug,' . $partner->id,
            'category' => 'required|string|max:100',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mou_number' => 'nullable|string|max:100',
            'personnel_count' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'remove_logo' => 'nullable|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_banner' => 'nullable|boolean',
        ]);

        // Tangani opsi penghapusan logo
        if ($request->boolean('remove_logo')) {
            if ($partner->logo_path && str_starts_with($partner->logo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $partner->logo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['logo_path'] = null;
        }

        // Tangani upload berkas logo baru
        if ($request->hasFile('logo')) {
            if ($partner->logo_path && str_starts_with($partner->logo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $partner->logo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('partners/logos', 'public');
            $validated['logo_path'] = '/storage/' . $path;
        }

        // Tangani opsi penghapusan banner
        if ($request->boolean('remove_banner')) {
            if ($partner->banner_path && str_starts_with($partner->banner_path, '/storage/')) {
                $oldBanner = str_replace('/storage/', '', $partner->banner_path);
                Storage::disk('public')->delete($oldBanner);
            }
            $validated['banner_path'] = null;
        }

        // Tangani upload berkas banner baru
        if ($request->hasFile('banner')) {
            if ($partner->banner_path && str_starts_with($partner->banner_path, '/storage/')) {
                $oldBanner = str_replace('/storage/', '', $partner->banner_path);
                Storage::disk('public')->delete($oldBanner);
            }
            $path = $request->file('banner')->store('partners/banners', 'public');
            $validated['banner_path'] = '/storage/' . $path;
        }

        unset($validated['logo'], $validated['remove_logo'], $validated['banner'], $validated['remove_banner']);

        $partner->update($validated);

        // Sinkronisasi nama user narahubung jika ada perubahan
        if (!empty($validated['pic_name'])) {
            $user->update([
                'name' => $partner->name . ' (' . $validated['pic_name'] . ')'
            ]);
        }

        return redirect()->back()->with('success', 'Profil lembaga berhasil diperbarui.');
    }

    /**
     * Tampilkan Halaman Pengaturan & Editor Konten Landing Page Mitra
     */
    public function landingPage(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra belum terdaftar.');
        }

        $allPartnerMembers = Volunteer::where('partner_id', $partner->id)->get();
        $totalVolunteers = $allPartnerMembers->count();
        $sarMissionsCount = count($partner->getSarParticipations());
        $partnerNews = News::where('partner_id', $partner->id)
            ->orderBy('id', 'desc')
            ->get();

        return Inertia::render('Partner/LandingPageSettings', [
            'partner' => $partner,
            'partnerNews' => $partnerNews,
            'stats' => [
                'totalVolunteers' => $totalVolunteers,
                'sarMissionsCount' => $sarMissionsCount,
            ],
            'landingPageUrl' => url('/mitra/' . ($partner->slug ?: $partner->id)),
        ]);
    }

    /**
     * Perbarui Konten Elemen Landing Page Mitra
     */
    public function updateLandingPage(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:partners,slug,' . $partner->id,
            'category' => 'required|string|max:100',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'mou_number' => 'nullable|string|max:100',
            'readiness_status' => 'nullable|string|max:100',
            'recruitment_status' => 'nullable|string|max:50',
            'personnel_count' => 'nullable|integer|min:0',
            'membership_terms' => 'nullable|string',
            'pillar_pre' => 'nullable|string',
            'pillar_during' => 'nullable|string',
            'pillar_post' => 'nullable|string',
            'pic_name' => 'required|string|max:255',
            'pic_phone' => 'required|string|max:50',
            'pic_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'remove_logo' => 'nullable|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'remove_banner' => 'nullable|boolean',
        ]);

        // Tangani opsi penghapusan logo
        if ($request->boolean('remove_logo')) {
            if ($partner->logo_path && str_starts_with($partner->logo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $partner->logo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['logo_path'] = null;
        }

        // Tangani upload berkas logo baru
        if ($request->hasFile('logo')) {
            if ($partner->logo_path && str_starts_with($partner->logo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $partner->logo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('partners/logos', 'public');
            $validated['logo_path'] = '/storage/' . $path;
        }

        // Tangani opsi penghapusan banner
        if ($request->boolean('remove_banner')) {
            if ($partner->banner_path && str_starts_with($partner->banner_path, '/storage/')) {
                $oldBanner = str_replace('/storage/', '', $partner->banner_path);
                Storage::disk('public')->delete($oldBanner);
            }
            $validated['banner_path'] = null;
        }

        // Tangani upload berkas banner baru
        if ($request->hasFile('banner')) {
            if ($partner->banner_path && str_starts_with($partner->banner_path, '/storage/')) {
                $oldBanner = str_replace('/storage/', '', $partner->banner_path);
                Storage::disk('public')->delete($oldBanner);
            }
            $path = $request->file('banner')->store('partners/banners', 'public');
            $validated['banner_path'] = '/storage/' . $path;
        }

        unset($validated['logo'], $validated['remove_logo'], $validated['banner'], $validated['remove_banner']);

        $partner->update($validated);

        if (!empty($validated['pic_name'])) {
            $user->update([
                'name' => $partner->name . ' (' . $validated['pic_name'] . ')'
            ]);
        }

        return redirect()->back()->with('success', 'Konten landing page berhasil diperbarui.');
    }

    /**
     * Tambah Berita & Artikel Khusus Lembaga Mitra
     */
    public function storeNews(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->back()->with('error', 'Lembaga mitra tidak valid.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'content' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'image_url' => 'nullable|string',
            'published_at' => 'nullable|string',
        ]);

        $validated['partner_id'] = $partner->id;
        $validated['author'] = $validated['author'] ?: ($partner->name . ' (' . ($partner->pic_name ?: 'Humas') . ')');
        $validated['published_at'] = $validated['published_at'] ?: date('Y-m-d');

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('news_images', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        unset($validated['image_file']);

        News::create($validated);

        return redirect()->back()->with('success', 'Berita / Artikel lembaga berhasil diterbitkan.');
    }

    /**
     * Perbarui Berita & Artikel Lembaga Mitra
     */
    public function updateNews(Request $request, News $news)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner || $news->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah artikel lembaga lain.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'content' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'image_url' => 'nullable|string',
            'published_at' => 'nullable|string',
        ]);

        if ($request->hasFile('image_file')) {
            if ($news->image_url && str_starts_with($news->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $news->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image_file')->store('news_images', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        unset($validated['image_file']);

        $news->update($validated);

        return redirect()->back()->with('success', 'Berita / Artikel lembaga berhasil diperbarui.');
    }

    /**
     * Hapus Berita & Artikel Lembaga Mitra
     */
    public function destroyNews(Request $request, News $news)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner || $news->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus artikel lembaga lain.');
        }

        if ($news->image_url && str_starts_with($news->image_url, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $news->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $news->delete();

        return redirect()->back()->with('success', 'Berita / Artikel lembaga berhasil dihapus.');
    }

    /**
     * Tampilkan Halaman Manajemen Pengurus & Anggota Lembaga Mitra
     */
    public function members(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra tidak ditemukan.');
        }

        $query = Volunteer::where('partner_id', $partner->id);

        // Search Filter
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('certifications', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        // Role Filter
        if ($request->filled('role') && $request->input('role') !== 'Semua') {
            $query->where('role', $request->input('role'));
        }

        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        // Blood Type Filter
        if ($request->filled('blood_type') && $request->input('blood_type') !== 'Semua') {
            $query->where('blood_type', $request->input('blood_type'));
        }

        $members = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        // Statistik Anggota Khusus Lembaga Ini
        $allPartnerMembers = Volunteer::where('partner_id', $partner->id)->get();
        $stats = [
            'total' => max($allPartnerMembers->count(), (int) $partner->personnel_count),
            'registered' => $allPartnerMembers->count(),
            'active' => $allPartnerMembers->where('status', 'Aktif')->count(),
            'rescue' => $allPartnerMembers->filter(fn($v) => str_contains($v->role, 'Rescue'))->count(),
            'donor' => $allPartnerMembers->filter(fn($v) => str_contains($v->role, 'Donor'))->count(),
            'medis' => $allPartnerMembers->filter(fn($v) => str_contains($v->role, 'Medis') || str_contains($v->role, 'Kesehatan'))->count(),
        ];

        $roles = [
            'Semua',
            'Ketua / Koordinator Lembaga',
            'Sekretaris Lembaga',
            'Bendahara Lembaga',
            'Koordinator Lapangan SAR',
            'Tim Rescue',
            'Tenaga Medis',
            'Donor Darah',
            'Anggota Personel',
            'Relawan Rescuer'
        ];

        return Inertia::render('Partner/Members', [
            'partner' => $partner,
            'members' => $members,
            'stats' => $stats,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role', 'status', 'blood_type'])
        ]);
    }

    /**
     * Tambah Pengurus / Anggota Baru Lembaga Mitra
     */
    public function storeMember(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner) {
            return redirect()->back()->with('error', 'Lembaga mitra tidak valid.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'required|string|max:100',
            'certifications' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['partner_id'] = $partner->id;
        $validated['registered_at'] = now()->toDateString();

        Volunteer::create($validated);

        return redirect()->back()->with('success', 'Data pengurus / anggota lembaga berhasil ditambahkan.');
    }

    /**
     * Perbarui Data Pengurus / Anggota Lembaga Mitra
     */
    public function updateMember(Request $request, Volunteer $volunteer)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        // Security check: Pastikan anggota ini milik lembaga yang sedang login
        if (!$partner || $volunteer->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah anggota lembaga lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'blood_type' => 'nullable|string|max:10',
            'role' => 'required|string|max:100',
            'certifications' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $volunteer->update($validated);

        return redirect()->back()->with('success', 'Data anggota lembaga berhasil diperbarui.');
    }

    /**
     * Hapus Data Pengurus / Anggota Lembaga Mitra
     */
    public function destroyMember(Request $request, Volunteer $volunteer)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        // Security check: Pastikan anggota ini milik lembaga yang sedang login
        if (!$partner || $volunteer->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus anggota lembaga lain.');
        }

        $volunteer->delete();

        return redirect()->back()->with('success', 'Anggota lembaga berhasil dihapus.');
    }

    /**
     * Tampilkan Halaman Inventaris Peralatan & Perlengkapan Lembaga Mitra
     */
    public function equipments(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner && ($user->isAdmin() || $user->isWebmaster())) {
            $partner = Partner::first();
        }

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Data lembaga mitra belum terdaftar.');
        }

        $query = $partner->equipments();

        // Pencarian teks
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('storage_location', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        // Filter Kondisi
        if ($request->filled('condition') && $request->input('condition') !== 'Semua') {
            $query->where('condition', $request->input('condition'));
        }

        // Filter Status Operasional
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        $equipments = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        // Ringkasan Statistik Peralatan
        $allEquipments = $partner->equipments()->get();
        $stats = [
            'total_items' => $allEquipments->count(),
            'total_quantity' => (int) $allEquipments->sum('quantity'),
            'ready_count' => $allEquipments->whereIn('condition', ['Siap Pakai', 'Baik'])->count(),
            'in_use_count' => $allEquipments->where('status', 'Sedang Digunakan')->count(),
            'maintenance_count' => $allEquipments->filter(fn($e) => in_array($e->condition, ['Rusak Ringan', 'Rusak Berat', 'Dalam Perawatan']) || $e->status === 'Maintenance')->count(),
            'water_vertical' => $allEquipments->whereIn('category', ['Water Rescue', 'Vertical Rescue'])->count(),
            'medical_shelter' => $allEquipments->whereIn('category', ['Medis & Evakuasi', 'Shelter & Tenda'])->count(),
        ];

        $categories = [
            'Water Rescue',
            'Vertical Rescue',
            'Medis & Evakuasi',
            'Komunikasi & Navigasi',
            'Penerangan & Kelistrikan',
            'Shelter & Tenda',
            'Dapur Umum & Logistik',
            'APD & Perlengkapan Pribadi',
            'Kendaraan & Alut',
            'Peralatan Ekstrikasi',
            'Lainnya',
        ];

        $conditions = [
            'Siap Pakai',
            'Baik',
            'Rusak Ringan',
            'Rusak Berat',
            'Dalam Perawatan',
        ];

        $statuses = [
            'Tersedia',
            'Sedang Digunakan',
            'Maintenance',
            'Tidak Aktif',
        ];

        $units = [
            'Unit',
            'Set',
            'Pcs',
            'Box',
            'Roll',
            'Paket',
            'Lembar',
        ];

        $ownershipStatuses = [
            'Milik Sendiri',
            'Pinjam Pakai',
            'Hibah / Bantuan',
            'Sewa',
        ];

        return Inertia::render('Partner/Equipments', [
            'partner' => $partner,
            'equipments' => $equipments,
            'stats' => $stats,
            'categories' => $categories,
            'conditions' => $conditions,
            'statuses' => $statuses,
            'units' => $units,
            'ownershipStatuses' => $ownershipStatuses,
            'filters' => $request->only(['search', 'category', 'condition', 'status']),
        ]);
    }

    /**
     * Tambah Peralatan / Perlengkapan Baru Lembaga Mitra
     */
    public function storeEquipment(Request $request)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner && ($user->isAdmin() || $user->isWebmaster())) {
            $partner = Partner::first();
        }

        if (!$partner) {
            return redirect()->back()->with('error', 'Lembaga mitra tidak valid.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'condition' => 'required|string|max:50',
            'storage_location' => 'nullable|string|max:255',
            'ownership_status' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'name.required' => 'Nama peralatan / perlengkapan wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'quantity.required' => 'Jumlah unit wajib diisi.',
            'quantity.min' => 'Jumlah unit minimal 0.',
            'unit.required' => 'Satuan wajib dipilih.',
            'condition.required' => 'Kondisi barang wajib dipilih.',
            'status.required' => 'Status operasional wajib dipilih.',
            'photo.image' => 'Berkas harus berupa gambar (JPEG, PNG, WebP).',
            'photo.max' => 'Ukuran gambar maksimal 3MB.',
        ]);

        if (empty($validated['item_code'])) {
            $maxId = ($partner->equipments()->max('id') ?? 0) + 1;
            $validated['item_code'] = 'EQ-' . str_pad($partner->id, 2, '0', STR_PAD_LEFT) . '-' . str_pad($maxId, 3, '0', STR_PAD_LEFT);
        } else {
            $validated['item_code'] = strtoupper(trim($validated['item_code']));
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('partners/equipments', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        $validated['partner_id'] = $partner->id;

        PartnerEquipment::create($validated);

        return redirect()->back()->with('success', 'Peralatan/perlengkapan berhasil ditambahkan ke inventaris.');
    }

    /**
     * Perbarui Data Peralatan / Perlengkapan Lembaga Mitra
     */
    public function updateEquipment(Request $request, PartnerEquipment $equipment)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner && ($user->isAdmin() || $user->isWebmaster())) {
            $partner = Partner::first();
        }

        // Security check: Pastikan peralatan ini milik lembaga yang sedang login
        if (!$partner || $equipment->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah peralatan lembaga lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'condition' => 'required|string|max:50',
            'storage_location' => 'nullable|string|max:255',
            'ownership_status' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_photo' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama peralatan / perlengkapan wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'quantity.required' => 'Jumlah unit wajib diisi.',
            'quantity.min' => 'Jumlah unit minimal 0.',
            'unit.required' => 'Satuan wajib dipilih.',
            'condition.required' => 'Kondisi barang wajib dipilih.',
            'status.required' => 'Status operasional wajib dipilih.',
            'photo.image' => 'Berkas harus berupa gambar (JPEG, PNG, WebP).',
            'photo.max' => 'Ukuran gambar maksimal 3MB.',
        ]);

        if (!empty($validated['item_code'])) {
            $validated['item_code'] = strtoupper(trim($validated['item_code']));
        }

        // Opsi penghapusan foto
        if ($request->boolean('remove_photo')) {
            if ($equipment->photo_path && str_starts_with($equipment->photo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $equipment->photo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['photo_path'] = null;
        }

        // Unggah foto baru
        if ($request->hasFile('photo')) {
            if ($equipment->photo_path && str_starts_with($equipment->photo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $equipment->photo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('partners/equipments', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Data peralatan/perlengkapan berhasil diperbarui.');
    }

    /**
     * Hapus Peralatan / Perlengkapan Lembaga Mitra
     */
    public function destroyEquipment(Request $request, PartnerEquipment $equipment)
    {
        $user = $request->user();
        $partner = $user->getPartner();

        if (!$partner && ($user->isAdmin() || $user->isWebmaster())) {
            $partner = Partner::first();
        }

        // Security check: Pastikan peralatan ini milik lembaga yang sedang login
        if (!$partner || $equipment->partner_id !== $partner->id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus peralatan lembaga lain.');
        }

        if ($equipment->photo_path && str_starts_with($equipment->photo_path, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $equipment->photo_path);
            Storage::disk('public')->delete($oldPath);
        }

        $equipment->delete();

        return redirect()->back()->with('success', 'Peralatan berhasil dihapus dari inventaris.');
    }
}
