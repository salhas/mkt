<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SarOperation;
use Illuminate\Http\Request;

class SarOperationApiController extends Controller
{
    /**
     * GET /api/v1/sar-operations - Daftar Operasi & Siaga SAR Berlangsung (Dapat Diakses Semua Role)
     */
    public function index(Request $request)
    {
        $query = SarOperation::query();

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('team_name', 'like', "%{$search}%")
                  ->orWhere('team_leader', 'like', "%{$search}%");
            });
        }

        $operations = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => $operations->count(),
            'data' => $operations
        ]);
    }

    /**
     * GET /api/v1/sar-operations/{id} - Detail Operasi SAR
     */
    public function show($id)
    {
        $operation = SarOperation::find($id);

        if (!$operation) {
            return response()->json([
                'success' => false,
                'message' => 'Data operasi SAR tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $operation
        ]);
    }

    /**
     * POST /api/v1/sar-operations - Tambah Operasi SAR Baru (Sanctum Protected)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'team_leader' => 'nullable|string|max:255',
            'status' => 'required|string|in:AKTIF,SIAGA,EVAKUASI,PEMULIHAN,SELESAI',
            'severity' => 'nullable|string|max:50',
            'victim_count' => 'nullable|integer|min:0',
            'equipment' => 'nullable|string',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'start_date' => 'nullable|string',
        ]);

        if (empty($validated['team_name'])) {
            $validated['team_name'] = 'Tim Rescue Gabungan MKT & BASARNAS';
        }

        if (empty($validated['team_leader'])) {
            $validated['team_leader'] = $request->user()->name ?? 'Ahmad Roni (Danpos SAR)';
        }

        if (empty($validated['start_date'])) {
            $validated['start_date'] = date('Y-m-d');
        }

        $operation = SarOperation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Operasi siaga SAR baru berhasil didaftarkan.',
            'data' => $operation
        ], 201);
    }

    /**
     * PUT /api/v1/sar-operations/{id} - Edit Operasi SAR (Sanctum Protected)
     */
    public function update(Request $request, $id)
    {
        $operation = SarOperation::find($id);

        if (!$operation) {
            return response()->json([
                'success' => false,
                'message' => 'Data operasi SAR tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'team_leader' => 'nullable|string|max:255',
            'status' => 'required|string|in:AKTIF,SIAGA,EVAKUASI,PEMULIHAN,SELESAI',
            'severity' => 'nullable|string|max:50',
            'victim_count' => 'nullable|integer|min:0',
            'equipment' => 'nullable|string',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'start_date' => 'nullable|string',
        ]);

        $operation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status operasi SAR berhasil diperbarui.',
            'data' => $operation
        ]);
    }

    /**
     * DELETE /api/v1/sar-operations/{id} - Hapus Operasi SAR (Sanctum Protected)
     */
    public function destroy(Request $request, $id)
    {
        $operation = SarOperation::find($id);

        if (!$operation) {
            return response()->json([
                'success' => false,
                'message' => 'Data operasi SAR tidak ditemukan.'
            ], 404);
        }

        $operation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data operasi SAR berhasil dihapus.'
        ]);
    }
}
