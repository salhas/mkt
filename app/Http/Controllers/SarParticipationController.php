<?php

namespace App\Http\Controllers;

use App\Models\SarOperation;
use App\Models\SarParticipation;
use Illuminate\Http\Request;

class SarParticipationController extends Controller
{
    private function authorizeParticipationOwner(SarParticipation $sarParticipation)
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // Webmaster & Administrator have full authorization
        if (in_array($user->role, ['webmaster', 'administrator'])) {
            return true;
        }

        // Check if current user is the owner of this participation entry
        if ($sarParticipation->user_id && $sarParticipation->user_id === $user->id) {
            return true;
        }

        // If user_id is null, allow if user name matches organization name
        if (!$sarParticipation->user_id && strtolower($user->name) === strtolower($sarParticipation->organization_name)) {
            return true;
        }

        abort(403, 'Akses Ditolak: Anda hanya memiliki hak akses untuk mengedit/mengubah data penugasan tim & sumber daya milik institusi/organisasi Anda sendiri.');
    }

    public function store(Request $request, SarOperation $sarOperation)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'commander_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:100',
            'personnel_count' => 'required|integer|min:1',
            'status' => 'required|string|in:Persiapan Mobilisasi,Dalam Perjalanan,Tiba di Posko Utama,Aktif Operasi Evakuasi,Selesai / Demobilisasi',
            'departure_location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'resources_deployed' => 'nullable|string',
            'preparation_notes' => 'nullable|string',
        ]);

        $validated['sar_operation_id'] = $sarOperation->id;
        $validated['user_id'] = auth()->id();

        SarParticipation::create($validated);

        // Update overall operation stats
        $sarOperation->increment('personnel_count', $validated['personnel_count']);

        return redirect()->back()->with('success', 'Berhasil merekam pendaftaran tim & partisipasi potensi SAR!');
    }

    public function update(Request $request, SarParticipation $sarParticipation)
    {
        $this->authorizeParticipationOwner($sarParticipation);

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'commander_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:100',
            'personnel_count' => 'required|integer|min:1',
            'status' => 'required|string|in:Persiapan Mobilisasi,Dalam Perjalanan,Tiba di Posko Utama,Aktif Operasi Evakuasi,Selesai / Demobilisasi',
            'departure_location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'resources_deployed' => 'nullable|string',
            'preparation_notes' => 'nullable|string',
        ]);

        $oldPersonnel = $sarParticipation->personnel_count;
        $sarParticipation->update($validated);

        $diff = $validated['personnel_count'] - $oldPersonnel;
        if ($diff != 0 && $sarParticipation->operation) {
            $sarParticipation->operation->increment('personnel_count', $diff);
        }

        return redirect()->back()->with('success', 'Status penugasan tim & sumber daya berhasil diperbarui.');
    }

    public function destroy(SarParticipation $sarParticipation)
    {
        $this->authorizeParticipationOwner($sarParticipation);

        if ($sarParticipation->operation) {
            $sarParticipation->operation->decrement('personnel_count', $sarParticipation->personnel_count);
        }

        $sarParticipation->delete();

        return redirect()->back()->with('success', 'Data partisipasi potensi SAR berhasil dihapus.');
    }
}
