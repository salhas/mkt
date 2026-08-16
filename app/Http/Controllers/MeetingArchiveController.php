<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MeetingArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::with('creator');

        // Search filter (Judul, pimpinan, notulis, lokasi, summary, agenda)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('leader', 'like', "%{$search}%")
                  ->orWhere('notewriter', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('agenda', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category') && $request->input('category') !== 'Semua') {
            $query->where('category', $request->input('category'));
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'Semua') {
            $query->where('status', $request->input('status'));
        }

        // Date filter
        if ($request->filled('start_date')) {
            $query->whereDate('meeting_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('meeting_date', '<=', $request->input('end_date'));
        }

        $meetings = $query->orderBy('meeting_date', 'desc')
                          ->paginate(10)
                          ->withQueryString();

        // Calculate statistics
        $allMeetings = Meeting::all();
        $totalMeetings = $allMeetings->count();
        $thisMonthCount = $allMeetings->filter(function ($m) {
            return $m->meeting_date && Carbon::parse($m->meeting_date)->isCurrentMonth();
        })->count();

        $totalActionItems = 0;
        $completedActionItems = 0;
        foreach ($allMeetings as $m) {
            if (is_array($m->action_items)) {
                foreach ($m->action_items as $item) {
                    $totalActionItems++;
                    if (!empty($item['completed'])) {
                        $completedActionItems++;
                    }
                }
            }
        }

        $categories = ['Rapat Koordinasi', 'Evaluasi Bencana', 'Rapat Pleno', 'Sosialisasi Donasi', 'Internal Tim'];
        $statuses = ['Selesai', 'Terjadwal', 'Draft', 'Diarsipkan'];

        // Active members & volunteers for combobox attendance select
        $activeVolunteers = \App\Models\Volunteer::where('status', 'Aktif')
            ->select('id', 'name', 'role', 'email')
            ->orderBy('name', 'asc')
            ->get();

        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name', 'asc')->get();

        // Merge unique names
        $activeMembers = collect();
        foreach ($activeVolunteers as $v) {
            $activeMembers->push([
                'id' => 'vol_' . $v->id,
                'name' => $v->name,
                'role' => $v->role ?? 'Relawan Aktif',
                'email' => $v->email,
            ]);
        }
        foreach ($users as $u) {
            if (!$activeMembers->pluck('name')->contains($u->name)) {
                $activeMembers->push([
                    'id' => 'usr_' . $u->id,
                    'name' => $u->name,
                    'role' => 'Pengurus / User Sistem',
                    'email' => $u->email,
                ]);
            }
        }

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'filters' => $request->only(['search', 'category', 'status', 'start_date', 'end_date']),
            'stats' => [
                'totalMeetings' => $totalMeetings,
                'thisMonthCount' => $thisMonthCount,
                'totalActionItems' => $totalActionItems,
                'completedActionItems' => $completedActionItems,
            ],
            'categories' => $categories,
            'statuses' => $statuses,
            'activeMembers' => $activeMembers->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'leader' => 'nullable|string|max:255',
            'notewriter' => 'nullable|string|max:255',
            'attendees' => 'nullable',
            'agenda' => 'nullable|string',
            'summary' => 'nullable|string',
            'action_items' => 'nullable',
            'status' => 'required|string|max:50',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('meeting_attachments', 'public');
            $validated['attachment_path'] = '/storage/' . $path;
        }

        // Format attendees & action_items if passed as JSON string
        if (is_string($request->input('attendees'))) {
            $validated['attendees'] = json_decode($request->input('attendees'), true) ?? array_map('trim', explode(',', $request->input('attendees')));
        }
        if (is_string($request->input('action_items'))) {
            $validated['action_items'] = json_decode($request->input('action_items'), true) ?? [];
        }

        $validated['created_by'] = auth()->id();

        Meeting::create($validated);

        return redirect()->back()->with('success', 'Arsip rapat berhasil ditambahkan.');
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'leader' => 'nullable|string|max:255',
            'notewriter' => 'nullable|string|max:255',
            'attendees' => 'nullable',
            'agenda' => 'nullable|string',
            'summary' => 'nullable|string',
            'action_items' => 'nullable',
            'status' => 'required|string|max:50',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            if ($meeting->attachment_path && Storage::disk('public')->exists(str_replace('/storage/', '', $meeting->attachment_path))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $meeting->attachment_path));
            }
            $path = $request->file('attachment')->store('meeting_attachments', 'public');
            $validated['attachment_path'] = '/storage/' . $path;
        }

        if (is_string($request->input('attendees'))) {
            $validated['attendees'] = json_decode($request->input('attendees'), true) ?? array_map('trim', explode(',', $request->input('attendees')));
        }
        if (is_string($request->input('action_items'))) {
            $validated['action_items'] = json_decode($request->input('action_items'), true) ?? [];
        }

        $meeting->update($validated);

        return redirect()->back()->with('success', 'Arsip rapat berhasil diperbarui.');
    }

    public function destroy(Meeting $meeting)
    {
        if ($meeting->attachment_path && Storage::disk('public')->exists(str_replace('/storage/', '', $meeting->attachment_path))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $meeting->attachment_path));
        }

        $meeting->delete();

        return redirect()->back()->with('success', 'Arsip rapat berhasil dihapus.');
    }
}
