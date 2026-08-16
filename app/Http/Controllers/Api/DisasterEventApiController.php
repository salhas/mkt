<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DisasterEvent;
use Illuminate\Http\Request;

class DisasterEventApiController extends Controller
{
    public function index(Request $request)
    {
        $query = DisasterEvent::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $events = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => count($events),
            'data' => $events
        ]);
    }

    public function show($id)
    {
        $event = DisasterEvent::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Kejadian Bencana tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'severity' => 'required|string|in:Rendah,Sedang,Tinggi,Kritis',
            'status' => 'required|string|max:100',
            'description' => 'nullable|string',
            'rescue_team_leader' => 'nullable|string|max:255',
            'victim_count' => 'nullable|integer',
            'date_occurred' => 'required|date',
        ]);

        $event = DisasterEvent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kejadian bencana berhasil dikirim.',
            'data' => $event
        ], 201);
    }
}
