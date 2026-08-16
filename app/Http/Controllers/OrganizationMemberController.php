<?php

namespace App\Http\Controllers;

use App\Models\OrganizationMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OrganizationMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganizationMember::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Tier filter
        if ($request->filled('tier') && $request->input('tier') !== 'Semua') {
            $query->where('tier', $request->input('tier'));
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        // Division filter
        if ($request->filled('division') && $request->input('division') !== 'Semua') {
            $query->where('division', $request->input('division'));
        }

        $members = $query->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(12)
            ->withQueryString();

        // Overall stats summary
        $stats = [
            'total_pembina' => OrganizationMember::where('tier', 'Dewan Pembina')->count(),
            'total_pengawas' => OrganizationMember::where('tier', 'Dewan Pengawas')->count(),
            'total_pengurus' => OrganizationMember::where('tier', 'Pengurus')->count(),
            'total_anggota' => OrganizationMember::where('tier', 'Anggota')->count(),
            'total_active' => OrganizationMember::where('status', 'Aktif')->count(),
        ];

        // Grouped members for Visual Organizational Chart view
        $chartData = [
            'pembina' => OrganizationMember::where('tier', 'Dewan Pembina')->where('status', 'Aktif')->orderBy('order_index', 'asc')->get(),
            'pengawas' => OrganizationMember::where('tier', 'Dewan Pengawas')->where('status', 'Aktif')->orderBy('order_index', 'asc')->get(),
            'pengurus' => OrganizationMember::where('tier', 'Pengurus')->where('status', 'Aktif')->orderBy('order_index', 'asc')->get(),
            'anggota' => OrganizationMember::where('tier', 'Anggota')->where('status', 'Aktif')->orderBy('order_index', 'asc')->limit(12)->get(),
        ];

        $tiers = ['Dewan Pembina', 'Dewan Pengawas', 'Pengurus', 'Anggota'];

        $divisions = [
            'Pembina & Pengarah Utama',
            'Pengawasan Audit & Operasional',
            'Pengurus Eksekutif',
            'Kesekretariatan & Administrasi',
            'Keuangan & Akuntansi',
            'Tim Operasional Rescue',
            'Layanan Kesehatan & Donor',
            'Humas & Public Relations',
            'Gudang & Distribusi',
            'Umum'
        ];

        return Inertia::render('Management/Index', [
            'members' => $members,
            'stats' => $stats,
            'chartData' => $chartData,
            'tiers' => $tiers,
            'divisions' => $divisions,
            'filters' => $request->only(['search', 'tier', 'status', 'division']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_number' => 'nullable|string|max:100|unique:organization_members,member_number',
            'name' => 'required|string|max:255',
            'tier' => 'required|string|in:Dewan Pembina,Dewan Pengawas,Pengurus,Anggota',
            'position' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|string|in:Aktif,Tidak Aktif,Demisioner',
            'period' => 'required|string|max:100',
            'order_index' => 'nullable|integer',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Auto-generate member_number if empty
        if (empty($validated['member_number'])) {
            $prefix = match ($validated['tier']) {
                'Dewan Pembina' => 'MKT-PB-',
                'Dewan Pengawas' => 'MKT-PW-',
                'Pengurus' => 'MKT-PG-',
                default => 'MKT-AG-',
            };
            $nextId = OrganizationMember::count() + 1;
            $validated['member_number'] = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('organization_photos', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        unset($validated['photo']);

        OrganizationMember::create($validated);

        return redirect()->back()->with('success', 'Anggota pengurus/relawan berhasil ditambahkan.');
    }

    public function update(Request $request, OrganizationMember $member)
    {
        $validated = $request->validate([
            'member_number' => 'nullable|string|max:100|unique:organization_members,member_number,' . $member->id,
            'name' => 'required|string|max:255',
            'tier' => 'required|string|in:Dewan Pembina,Dewan Pengawas,Pengurus,Anggota',
            'position' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|string|in:Aktif,Tidak Aktif,Demisioner',
            'period' => 'required|string|max:100',
            'order_index' => 'nullable|integer',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($member->photo_path && str_starts_with($member->photo_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $member->photo_path);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('organization_photos', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        unset($validated['photo']);

        $member->update($validated);

        return redirect()->back()->with('success', 'Data pengurus/anggota berhasil diperbarui.');
    }

    public function destroy(OrganizationMember $member)
    {
        if ($member->photo_path && str_starts_with($member->photo_path, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $member->photo_path);
            Storage::disk('public')->delete($oldPath);
        }

        $member->delete();

        return redirect()->back()->with('success', 'Data pengurus/anggota berhasil dihapus.');
    }
}
