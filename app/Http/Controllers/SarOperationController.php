<?php

namespace App\Http\Controllers;

use App\Models\SarOperation;
use App\Models\SarParticipation;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SarOperationController extends Controller
{
    private function authorizeSarCrud()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['webmaster', 'administrator', 'relawan', 'mitra', 'medis'])) {
            abort(403, 'Akses Ditolak: Peran Anda tidak memiliki hak akses untuk menambah atau mengedit data Operasi & Siaga SAR.');
        }
    }

    public function index(Request $request)
    {
        $query = SarOperation::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('commander_name', 'like', "%{$search}%")
                  ->orWhere('potensi_sar', 'like', "%{$search}%")
                  ->orWhere('deployed_teams', 'like', "%{$search}%")
                  ->orWhere('standby_teams', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && $request->input('type') !== 'Semua') {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity') && $request->input('severity') !== 'Semua') {
            $query->where('severity_level', $request->input('severity'));
        }

        $operations = $query->with('participations')->orderBy('start_date', 'desc')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total_all' => SarOperation::count(),
            'total_operasi' => SarOperation::where('type', 'Operasi SAR')->count(),
            'total_siaga' => SarOperation::where('type', 'Siaga SAR')->count(),
            'total_aktif' => SarOperation::where('status', 'Operasi Aktif')->count(),
            'total_personnel' => SarOperation::where('status', 'Operasi Aktif')->sum('personnel_count'),
            'total_victims_saved' => SarOperation::sum('victims_saved'),
        ];

        return Inertia::render('SarOperations/Index', [
            'operations' => $operations,
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'status', 'severity'])
        ]);
    }

    public function commandCenter(Request $request)
    {
        $activeOperations = SarOperation::with('participations')
            ->orderBy('start_date', 'desc')
            ->get();

        $stats = [
            'total_active_ops' => SarOperation::whereIn('status', ['Operasi Aktif', 'Siaga SAR'])->count(),
            'total_personnel' => SarOperation::sum('personnel_count'),
            'total_teams' => SarParticipation::count(),
            'total_saved' => SarOperation::sum('victims_saved'),
            'total_injured' => SarOperation::sum('victims_injured'),
            'total_deceased' => SarOperation::sum('victims_deceased'),
            'total_missing' => SarOperation::sum('victims_missing'),
        ];

        return Inertia::render('SarOperations/CommandCenter', [
            'operations' => $activeOperations,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeSarCrud();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Operasi SAR,Siaga SAR',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|max:100',
            'severity_level' => 'required|string|max:100',
            'commander_name' => 'nullable|string|max:255',
            'personnel_count' => 'required|integer|min:1',
            'potensi_sar' => 'nullable|string',
            'deployed_teams' => 'nullable|string',
            'standby_teams' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'victims_saved' => 'nullable|integer|min:0',
            'victims_injured' => 'nullable|integer|min:0',
            'victims_deceased' => 'nullable|integer|min:0',
            'victims_missing' => 'nullable|integer|min:0',
        ]);

        $validated['code'] = 'SAR-' . date('Ym') . '-' . sprintf('%03d', SarOperation::count() + 1);
        $validated['latitude'] = $validated['latitude'] ?? -5.147665;
        $validated['longitude'] = $validated['longitude'] ?? 119.432731;

        SarOperation::create($validated);

        return redirect()->back()->with('success', 'Data Operasi & Siaga SAR berhasil ditambahkan.');
    }

    public function update(Request $request, SarOperation $sarOperation)
    {
        $this->authorizeSarCrud();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Operasi SAR,Siaga SAR',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|max:100',
            'severity_level' => 'required|string|max:100',
            'commander_name' => 'nullable|string|max:255',
            'personnel_count' => 'required|integer|min:1',
            'potensi_sar' => 'nullable|string',
            'deployed_teams' => 'nullable|string',
            'standby_teams' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'victims_saved' => 'nullable|integer|min:0',
            'victims_injured' => 'nullable|integer|min:0',
            'victims_deceased' => 'nullable|integer|min:0',
            'victims_missing' => 'nullable|integer|min:0',
        ]);

        $sarOperation->update($validated);

        return redirect()->back()->with('success', 'Data Operasi & Siaga SAR berhasil diperbarui.');
    }

    public function destroy(SarOperation $sarOperation)
    {
        $this->authorizeSarCrud();

        $sarOperation->delete();

        return redirect()->back()->with('success', 'Data Operasi SAR berhasil dihapus.');
    }
}
