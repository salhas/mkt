<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationMemberApiController extends Controller
{
    public function index(Request $request)
    {
        $query = OrganizationMember::query();

        if ($request->filled('tier') && $request->input('tier') !== 'Semua') {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        } else if (!$request->filled('status')) {
            // Default: show active if not specified, or allow all if include_inactive is true
            if (!$request->boolean('all')) {
                $query->where('status', 'Aktif');
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('division', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $allMembers = OrganizationMember::orderBy('order_index', 'asc')->get();

        $chartData = [
            'pembina' => $members->where('tier', 'Dewan Pembina')->values(),
            'pengawas' => $members->where('tier', 'Dewan Pengawas')->values(),
            'pengurus' => $members->where('tier', 'Pengurus')->values(),
            'anggota' => $members->where('tier', 'Anggota')->values(),
        ];

        $stats = [
            'total_all' => $allMembers->count(),
            'total_pembina' => $allMembers->where('tier', 'Dewan Pembina')->count(),
            'total_pengawas' => $allMembers->where('tier', 'Dewan Pengawas')->count(),
            'total_pengurus' => $allMembers->where('tier', 'Pengurus')->count(),
            'total_anggota' => $allMembers->where('tier', 'Anggota')->count(),
            'total_active' => $allMembers->where('status', 'Aktif')->count(),
        ];

        return response()->json([
            'success' => true,
            'count' => count($members),
            'stats' => $stats,
            'structure' => $chartData,
            'all_members' => $members
        ]);
    }

    public function show($id)
    {
        $member = OrganizationMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengurus tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $member
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
            'period' => 'nullable|string|max:100',
            'order_index' => 'nullable|integer',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if (empty($validated['period'])) {
            $validated['period'] = '2024 - 2029';
        }

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

        $member = OrganizationMember::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Pengurus MKT berhasil ditambahkan.',
            'data' => $member
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $member = OrganizationMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengurus tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'member_number' => 'nullable|string|max:100|unique:organization_members,member_number,' . $member->id,
            'name' => 'sometimes|required|string|max:255',
            'tier' => 'sometimes|required|string|in:Dewan Pembina,Dewan Pengawas,Pengurus,Anggota',
            'position' => 'sometimes|required|string|max:255',
            'division' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'sometimes|required|string|in:Aktif,Tidak Aktif,Demisioner',
            'period' => 'nullable|string|max:100',
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

        return response()->json([
            'success' => true,
            'message' => 'Data Pengurus MKT berhasil diperbarui.',
            'data' => $member
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $member = OrganizationMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengurus tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Aktif,Tidak Aktif,Demisioner',
        ]);

        $member->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Status pengurus berhasil diubah menjadi {$validated['status']}.",
            'data' => $member
        ]);
    }

    public function destroy($id)
    {
        $member = OrganizationMember::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengurus tidak ditemukan.'
            ], 404);
        }

        if ($member->photo_path && str_starts_with($member->photo_path, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $member->photo_path);
            Storage::disk('public')->delete($oldPath);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Pengurus berhasil dihapus.'
        ]);
    }
}

