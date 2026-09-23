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
        $query = SarOperation::query()->with('participations');

        if ($request->filled('type') && $request->input('type') !== 'Semua') {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity') && $request->input('severity') !== 'Semua') {
            $query->where('severity_level', $request->input('severity'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('commander_name', 'like', "%{$search}%")
                  ->orWhere('potensi_sar', 'like', "%{$search}%")
                  ->orWhere('deployed_teams', 'like', "%{$search}%")
                  ->orWhere('standby_teams', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $operations = $query->orderBy('start_date', 'desc')->orderBy('id', 'desc')->get();

        $stats = [
            'total_all' => SarOperation::count(),
            'total_operasi' => SarOperation::where('type', 'Operasi SAR')->count(),
            'total_siaga' => SarOperation::where('type', 'Siaga SAR')->count(),
            'total_aktif' => SarOperation::whereIn('status', ['Operasi Aktif', 'Siaga SAR', 'AKTIF', 'SIAGA'])->count(),
            'total_personnel' => SarOperation::whereIn('status', ['Operasi Aktif', 'Siaga SAR', 'AKTIF', 'SIAGA'])->sum('personnel_count'),
            'total_victims_saved' => SarOperation::sum('victims_saved'),
        ];

        return response()->json([
            'success' => true,
            'count' => $operations->count(),
            'stats' => $stats,
            'data' => $operations
        ]);
    }

    /**
     * GET /api/v1/sar-operations/{id} - Detail Operasi SAR
     */
    public function show($id)
    {
        $operation = SarOperation::with('participations')
            ->where('id', $id)
            ->orWhere('code', $id)
            ->first();

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
            'type' => 'nullable|string|in:Operasi SAR,Siaga SAR',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|string|max:100',
            'severity_level' => 'nullable|string|max:100',
            'severity' => 'nullable|string|max:100',
            'commander_name' => 'nullable|string|max:255',
            'team_leader' => 'nullable|string|max:255',
            'personnel_count' => 'nullable|integer|min:1',
            'potensi_sar' => 'nullable|string',
            'team_name' => 'nullable|string|max:255',
            'deployed_teams' => 'nullable|string',
            'standby_teams' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'equipment' => 'nullable|string',
            'victims_saved' => 'nullable|integer|min:0',
            'victim_count' => 'nullable|integer|min:0',
            'victims_injured' => 'nullable|integer|min:0',
            'victims_deceased' => 'nullable|integer|min:0',
            'victims_missing' => 'nullable|integer|min:0',
        ]);

        $code = 'SAR-' . date('Ym') . '-' . sprintf('%03d', SarOperation::count() + 1);

        $data = [
            'code' => $code,
            'title' => $validated['title'],
            'type' => $validated['type'] ?? 'Operasi SAR',
            'location' => $validated['location'],
            'latitude' => $validated['latitude'] ?? -5.147665,
            'longitude' => $validated['longitude'] ?? 119.432731,
            'status' => $validated['status'] ?? 'Operasi Aktif',
            'severity_level' => $validated['severity_level'] ?? $validated['severity'] ?? 'Tinggi',
            'commander_name' => $validated['commander_name'] ?? $validated['team_leader'] ?? ($request->user() ? $request->user()->name : 'Ahmad Roni (Danpos SAR)'),
            'personnel_count' => $validated['personnel_count'] ?? 1,
            'potensi_sar' => $validated['potensi_sar'] ?? $validated['team_name'] ?? 'Tim Rescue Gabungan MKT & Potensi SAR',
            'deployed_teams' => $validated['deployed_teams'] ?? null,
            'standby_teams' => $validated['standby_teams'] ?? null,
            'start_date' => $validated['start_date'] ?? date('Y-m-d'),
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'equipment_used' => $validated['equipment_used'] ?? $validated['equipment'] ?? null,
            'victims_saved' => $validated['victims_saved'] ?? $validated['victim_count'] ?? 0,
            'victims_injured' => $validated['victims_injured'] ?? 0,
            'victims_deceased' => $validated['victims_deceased'] ?? 0,
            'victims_missing' => $validated['victims_missing'] ?? 0,
        ];

        $operation = SarOperation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Operasi siaga SAR baru berhasil didaftarkan.',
            'data' => $operation
        ], 201);
    }

    /**
     * PUT/PATCH /api/v1/sar-operations/{id} - Edit Operasi SAR (Sanctum Protected)
     */
    public function update(Request $request, $id)
    {
        $operation = SarOperation::where('id', $id)->orWhere('code', $id)->first();

        if (!$operation) {
            return response()->json([
                'success' => false,
                'message' => 'Data operasi SAR tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|in:Operasi SAR,Siaga SAR',
            'location' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|string|max:100',
            'severity_level' => 'nullable|string|max:100',
            'severity' => 'nullable|string|max:100',
            'commander_name' => 'nullable|string|max:255',
            'team_leader' => 'nullable|string|max:255',
            'personnel_count' => 'nullable|integer|min:1',
            'potensi_sar' => 'nullable|string',
            'team_name' => 'nullable|string|max:255',
            'deployed_teams' => 'nullable|string',
            'standby_teams' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'equipment' => 'nullable|string',
            'victims_saved' => 'nullable|integer|min:0',
            'victim_count' => 'nullable|integer|min:0',
            'victims_injured' => 'nullable|integer|min:0',
            'victims_deceased' => 'nullable|integer|min:0',
            'victims_missing' => 'nullable|integer|min:0',
        ]);

        $data = [];
        if (isset($validated['title'])) $data['title'] = $validated['title'];
        if (isset($validated['type'])) $data['type'] = $validated['type'];
        if (isset($validated['location'])) $data['location'] = $validated['location'];
        if (isset($validated['latitude'])) $data['latitude'] = $validated['latitude'];
        if (isset($validated['longitude'])) $data['longitude'] = $validated['longitude'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];
        if (isset($validated['severity_level'])) {
            $data['severity_level'] = $validated['severity_level'];
        } elseif (isset($validated['severity'])) {
            $data['severity_level'] = $validated['severity'];
        }

        if (isset($validated['commander_name'])) {
            $data['commander_name'] = $validated['commander_name'];
        } elseif (isset($validated['team_leader'])) {
            $data['commander_name'] = $validated['team_leader'];
        }

        if (isset($validated['personnel_count'])) $data['personnel_count'] = $validated['personnel_count'];
        if (isset($validated['potensi_sar'])) {
            $data['potensi_sar'] = $validated['potensi_sar'];
        } elseif (isset($validated['team_name'])) {
            $data['potensi_sar'] = $validated['team_name'];
        }

        if (array_key_exists('deployed_teams', $validated)) $data['deployed_teams'] = $validated['deployed_teams'];
        if (array_key_exists('standby_teams', $validated)) $data['standby_teams'] = $validated['standby_teams'];
        if (isset($validated['start_date'])) $data['start_date'] = $validated['start_date'];
        if (array_key_exists('end_date', $validated)) $data['end_date'] = $validated['end_date'];
        if (array_key_exists('description', $validated)) $data['description'] = $validated['description'];

        if (isset($validated['equipment_used'])) {
            $data['equipment_used'] = $validated['equipment_used'];
        } elseif (isset($validated['equipment'])) {
            $data['equipment_used'] = $validated['equipment'];
        }

        if (isset($validated['victims_saved'])) {
            $data['victims_saved'] = $validated['victims_saved'];
        } elseif (isset($validated['victim_count'])) {
            $data['victims_saved'] = $validated['victim_count'];
        }

        if (isset($validated['victims_injured'])) $data['victims_injured'] = $validated['victims_injured'];
        if (isset($validated['victims_deceased'])) $data['victims_deceased'] = $validated['victims_deceased'];
        if (isset($validated['victims_missing'])) $data['victims_missing'] = $validated['victims_missing'];

        $operation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Status operasi SAR berhasil diperbarui.',
            'data' => $operation->fresh('participations')
        ]);
    }

    /**
     * DELETE /api/v1/sar-operations/{id} - Hapus Operasi SAR (Sanctum Protected)
     */
    public function destroy(Request $request, $id)
    {
        $operation = SarOperation::where('id', $id)->orWhere('code', $id)->first();

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
