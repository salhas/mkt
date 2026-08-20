<?php

namespace App\Http\Controllers;

use App\Models\DisasterEvent;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DisasterMapController extends Controller
{
    public function index(Request $request)
    {
        $events = DisasterEvent::orderBy('date_occurred', 'desc')->get();
        
        $bmkgController = app(\App\Http\Controllers\Api\BmkgApiController::class);
        $bmkgData = $bmkgController->getEarthquakes($request)->getData(true);

        return Inertia::render('DisasterMap/Index', [
            'events' => $events,
            'bmkgData' => $bmkgData,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'severity' => 'required|string|in:Rendah,Sedang,Tinggi,Darurat',
            'status' => 'required|string|in:Siaga,Evakuasi,Pemulihan,Selesai',
            'description' => 'nullable|string',
            'rescue_team_leader' => 'nullable|string|max:255',
            'victim_count' => 'required|integer|min:0',
            'date_occurred' => 'required|date',
        ]);

        DisasterEvent::create($validated);

        return redirect()->back()->with('success', 'Titik operasi tanggap bencana berhasil ditambahkan.');
    }

    public function update(Request $request, DisasterEvent $disasterEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'severity' => 'required|string|in:Rendah,Sedang,Tinggi,Darurat',
            'status' => 'required|string|in:Siaga,Evakuasi,Pemulihan,Selesai',
            'description' => 'nullable|string',
            'rescue_team_leader' => 'nullable|string|max:255',
            'victim_count' => 'required|integer|min:0',
            'date_occurred' => 'required|date',
        ]);

        $disasterEvent->update($validated);

        return redirect()->back()->with('success', 'Titik operasi tanggap bencana berhasil diperbarui.');
    }

    public function destroy(DisasterEvent $disasterEvent)
    {
        $disasterEvent->delete();
        return redirect()->back()->with('success', 'Titik operasi tanggap bencana berhasil dihapus.');
    }
}
