<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingAttendanceController extends Controller
{
    /**
     * Tampilkan formulir publik presensi mandiri (untuk peserta/undangan via QR Code)
     */
    public function showPublicForm(string $token)
    {
        $meeting = Meeting::where('attendance_token', $token)->first();

        if (!$meeting) {
            abort(404, 'Kegiatan atau Agenda tidak ditemukan.');
        }

        // Hitung total peserta yang telah hadir
        $totalAttended = $meeting->attendances()->count();

        return Inertia::render('Public/MeetingAttendance', [
            'meeting' => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'meeting_date' => $meeting->meeting_date,
                'location' => $meeting->location,
                'category' => $meeting->category,
                'leader' => $meeting->leader,
                'status' => $meeting->status,
                'attendance_token' => $meeting->attendance_token,
                'is_attendance_open' => $meeting->is_attendance_open,
                'total_attended' => $totalAttended,
                'post_attendance_image' => $meeting->post_attendance_image,
                'post_attendance_message' => $meeting->post_attendance_message,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'submittedData' => session('submittedData'),
            ]
        ]);
    }

    /**
     * Simpan data presensi mandiri dari peserta/undangan
     */
    public function submitPublicAttendance(Request $request, string $token)
    {
        $meeting = Meeting::where('attendance_token', $token)->firstOrFail();

        if (!$meeting->is_attendance_open) {
            return redirect()->back()->with('error', 'Mohon maaf, presensi kehadiran untuk kegiatan ini telah ditutup oleh panitia.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'institution' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'signature' => 'nullable|string', // Base64 Canvas dataURL
            'notes' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi untuk verifikasi kehadiran.',
        ]);

        $validated['meeting_id'] = $meeting->id;
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['attended_at'] = now();

        $attendance = MeetingAttendance::create($validated);

        // Sinkronisasi nama ke array attendees pada meeting jika belum ada
        $attendees = is_array($meeting->attendees) ? $meeting->attendees : [];
        if (!in_array($validated['name'], $attendees)) {
            $attendees[] = $validated['name'];
            $meeting->update(['attendees' => $attendees]);
        }

        return redirect()->back()->with([
            'success' => 'Presensi kehadiran Anda berhasil dicatat. Terima kasih telah berpartisipasi!',
            'submittedData' => [
                'id' => $attendance->id,
                'name' => $attendance->name,
                'institution' => $attendance->institution ?? 'Umum / Mandiri',
                'attended_at' => $attendance->attended_at->format('d M Y - H:i') . ' WITA',
                'meeting_title' => $meeting->title,
            ]
        ]);
    }

    /**
     * Admin: Toggle status buka/tutup presensi
     */
    public function toggleAttendance(Meeting $meeting)
    {
        $meeting->is_attendance_open = !$meeting->is_attendance_open;
        $meeting->save();

        $statusText = $meeting->is_attendance_open ? 'dibuka' : 'ditutup';
        return redirect()->back()->with('success', "Presensi kegiatan berhasil {$statusText}.");
    }

    /**
     * Admin: Ambil daftar kehadiran peserta untuk meeting tertentu (JSON API)
     */
    public function getAttendances(Meeting $meeting)
    {
        $meeting->ensureAttendanceToken();

        $attendances = $meeting->attendances()
            ->select('id', 'meeting_id', 'name', 'phone', 'email', 'institution', 'position', 'signature', 'notes', 'attended_at')
            ->get();

        return response()->json([
            'success' => true,
            'meeting' => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'attendance_token' => $meeting->attendance_token,
                'is_attendance_open' => $meeting->is_attendance_open,
                'public_url' => route('public.attendance.show', $meeting->attendance_token),
            ],
            'attendances' => $attendances,
            'total' => $attendances->count(),
        ]);
    }

    /**
     * Admin: Hapus catatan kehadiran peserta
     */
    public function destroyAttendance(MeetingAttendance $attendance)
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Catatan kehadiran peserta berhasil dihapus.');
    }

    /**
     * Admin: Export daftar absensi ke file CSV
     */
    public function exportAttendances(Meeting $meeting): StreamedResponse
    {
        $attendances = $meeting->attendances()->get();
        $fileName = 'Daftar_Hadir_' . \Illuminate\Support\Str::slug($meeting->title) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($meeting, $attendances) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Judul dan Metadata Laporan
            fputcsv($handle, ['DAFTAR HADIR PESERTA & UNDANGAN - YAYASAN MKT INDONESIA']);
            fputcsv($handle, ['Kegiatan', $meeting->title]);
            fputcsv($handle, ['Waktu Pelaksanaan', $meeting->meeting_date ? $meeting->meeting_date->format('d-m-Y H:i') : '-']);
            fputcsv($handle, ['Lokasi', $meeting->location ?? '-']);
            fputcsv($handle, ['Total Peserta Hadir', $attendances->count()]);
            fputcsv($handle, []); // Baris kosong

            // Header Tabel
            fputcsv($handle, [
                'No',
                'Nama Lengkap',
                'Instansi / Lembaga / Unsur',
                'Jabatan / Peran',
                'Nomor WhatsApp / HP',
                'Email',
                'Waktu Presensi (WITA)',
                'Catatan / Pesan',
            ]);

            // Data Rows
            foreach ($attendances as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->name,
                    $item->institution ?? '-',
                    $item->position ?? '-',
                    $item->phone ?? '-',
                    $item->email ?? '-',
                    $item->attended_at ? $item->attended_at->format('d-m-Y H:i:s') : '-',
                    $item->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
