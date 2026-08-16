<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingApiController extends Controller
{
    /**
     * Display a listing of internal management meetings.
     */
    public function index(Request $request)
    {
        $query = Meeting::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $meetings = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'count' => count($meetings),
            'data' => $meetings
        ]);
    }

    /**
     * Display the specified meeting.
     */
    public function show($id)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Agenda/Arsip Rapat tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $meeting
        ]);
    }

    /**
     * Store a newly created meeting agenda (Sanctum Auth / Webmaster).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'leader' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'meeting_link' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:100',
        ]);

        $meeting = Meeting::create([
            'title' => $validated['title'],
            'meeting_date' => $validated['meeting_date'],
            'location' => $validated['location'],
            'category' => $validated['category'] ?? 'Mendatang',
            'leader' => $validated['leader'] ?? 'Manajemen MKT',
            'summary' => $validated['summary'] ?? '',
            'attachment_path' => $validated['meeting_link'] ?? '',
            'status' => $validated['status'] ?? 'Mendatang',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agenda rapat baru berhasil ditambahkan.',
            'data' => $meeting
        ], 201);
    }
}
