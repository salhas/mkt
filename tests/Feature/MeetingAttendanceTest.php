<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MeetingAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_attendance_form_can_be_viewed_with_valid_token(): void
    {
        $meeting = Meeting::create([
            'title' => 'Rapat Koordinasi Siaga Bencana',
            'meeting_date' => now()->addDay(),
            'location' => 'Posko Insignia Oasis',
            'category' => 'Koordinasi Posko',
            'status' => 'Terjadwal',
            'attendance_token' => 'sample-valid-token-12345678',
            'is_attendance_open' => true,
        ]);

        $response = $this->get(route('public.attendance.show', 'sample-valid-token-12345678'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/MeetingAttendance')
            ->where('meeting.id', $meeting->id)
            ->where('meeting.title', 'Rapat Koordinasi Siaga Bencana')
            ->where('meeting.attendance_token', 'sample-valid-token-12345678')
            ->where('meeting.is_attendance_open', true)
        );
    }

    public function test_public_attendance_form_aborts_when_token_invalid(): void
    {
        $response = $this->get(route('public.attendance.show', 'non-existent-token'));
        $response->assertNotFound();
    }

    public function test_participant_can_submit_public_attendance_successfully(): void
    {
        $meeting = Meeting::create([
            'title' => 'Apel Relawan SAR Gabungan',
            'meeting_date' => now()->addDays(2),
            'location' => 'Lapangan Karebosi',
            'category' => 'Agenda Kegiatan / Baksos',
            'status' => 'Terjadwal',
            'attendance_token' => 'token-apel-sar-9988',
            'is_attendance_open' => true,
            'attendees' => ['Kapten Hadi'],
        ]);

        $postData = [
            'name' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'email' => 'ahmad@example.com',
            'institution' => 'BASARNAS',
            'position' => 'Rescuer',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'notes' => 'Hadir tepat waktu mewakili tim 1',
        ];

        $response = $this->post(route('public.attendance.submit', 'token-apel-sar-9988'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('meeting_attendances', [
            'meeting_id' => $meeting->id,
            'name' => 'Ahmad Fauzi',
            'phone' => '081234567890',
            'institution' => 'BASARNAS',
        ]);

        // Check attendees array synchronized
        $meeting->refresh();
        $this->assertContains('Ahmad Fauzi', $meeting->attendees);
        $this->assertContains('Kapten Hadi', $meeting->attendees);
    }

    public function test_cannot_submit_attendance_when_meeting_attendance_is_closed(): void
    {
        $meeting = Meeting::create([
            'title' => 'Rapat Pleno Yayasan MKT',
            'meeting_date' => now()->subDay(),
            'location' => 'Kantor Pusat',
            'status' => 'Selesai',
            'attendance_token' => 'token-pleno-tertutup',
            'is_attendance_open' => false,
        ]);

        $postData = [
            'name' => 'Peserta Terlambat',
            'phone' => '081999888777',
        ];

        $response = $this->post(route('public.attendance.submit', 'token-pleno-tertutup'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Mohon maaf, presensi kehadiran untuk kegiatan ini telah ditutup oleh panitia.');

        $this->assertDatabaseMissing('meeting_attendances', [
            'name' => 'Peserta Terlambat',
        ]);
    }

    public function test_admin_can_toggle_meeting_attendance_status(): void
    {
        $admin = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Koordinasi Pos Dapur Umum',
            'meeting_date' => now()->addDays(1),
            'status' => 'Terjadwal',
            'attendance_token' => 'token-dapur-umum',
            'is_attendance_open' => true,
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('meetings.attendances.toggle', $meeting->id));

        $response->assertRedirect();
        $meeting->refresh();
        $this->assertFalse($meeting->is_attendance_open);

        // Toggle back to open
        $response2 = $this
            ->actingAs($admin)
            ->patch(route('meetings.attendances.toggle', $meeting->id));

        $response2->assertRedirect();
        $meeting->refresh();
        $this->assertTrue($meeting->is_attendance_open);
    }

    public function test_admin_can_retrieve_attendees_json_data(): void
    {
        $admin = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Briefing Lapangan Operasi SAR',
            'meeting_date' => now(),
            'status' => 'Terjadwal',
            'attendance_token' => 'token-sar-json',
            'is_attendance_open' => true,
            'created_by' => $admin->id,
        ]);

        MeetingAttendance::create([
            'meeting_id' => $meeting->id,
            'name' => 'Sersan Budi',
            'phone' => '081211112222',
            'institution' => 'TNI AL',
            'position' => 'Komandan Tim',
            'attended_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('meetings.attendances.data', $meeting->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'meeting' => [
                'id' => $meeting->id,
                'attendance_token' => 'token-sar-json',
                'is_attendance_open' => true,
            ],
            'total' => 1,
        ]);
        $response->assertJsonFragment([
            'name' => 'Sersan Budi',
            'phone' => '081211112222',
            'institution' => 'TNI AL',
        ]);
    }

    public function test_admin_can_delete_attendance_record(): void
    {
        $admin = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Rapat Kerja Bulanan',
            'meeting_date' => now(),
            'status' => 'Terjadwal',
            'created_by' => $admin->id,
        ]);

        $attendance = MeetingAttendance::create([
            'meeting_id' => $meeting->id,
            'name' => 'Data Salah Ketik',
            'phone' => '081200000000',
            'attended_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('meetings.attendances.destroy', $attendance->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('meeting_attendances', [
            'id' => $attendance->id,
        ]);
    }

    public function test_admin_can_export_attendees_csv(): void
    {
        $admin = User::factory()->create();

        $meeting = Meeting::create([
            'title' => 'Simulasi Tanggap Bencana Gempa',
            'meeting_date' => now(),
            'status' => 'Terjadwal',
            'created_by' => $admin->id,
        ]);

        MeetingAttendance::create([
            'meeting_id' => $meeting->id,
            'name' => 'Rina Wijaya',
            'phone' => '081398765432',
            'institution' => 'PMI Cabang',
            'position' => 'Divisi Medis',
            'attended_at' => now(),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('meetings.attendances.export', $meeting->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('content-disposition'));
    }

    public function test_can_create_meeting_with_post_attendance_image_and_message(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::factory()->create();

        $file = \Illuminate\Http\UploadedFile::fake()->image('denah_lokasi.png');

        $response = $this
            ->actingAs($admin)
            ->post(route('meetings.store'), [
                'title' => 'Simulasi Gabungan SAR & Medis',
                'meeting_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'category' => 'Agenda Kegiatan / Baksos',
                'status' => 'Terjadwal',
                'post_attendance_image' => $file,
                'post_attendance_message' => 'Selamat datang! Silakan menuju Posko Medis di Lt. 2 atau gabung grup WA: https://chat.whatsapp.com/test',
            ]);

        $response->assertRedirect();
        
        $meeting = Meeting::where('title', 'Simulasi Gabungan SAR & Medis')->first();
        $this->assertNotNull($meeting);
        $this->assertNotNull($meeting->post_attendance_image);
        $this->assertEquals('Selamat datang! Silakan menuju Posko Medis di Lt. 2 atau gabung grup WA: https://chat.whatsapp.com/test', $meeting->post_attendance_message);

        // Verify public attendance form shows this image and message
        $publicRes = $this->get(route('public.attendance.show', $meeting->attendance_token));
        $publicRes->assertOk();
        $publicRes->assertInertia(fn (Assert $page) => $page
            ->component('Public/MeetingAttendance')
            ->where('meeting.post_attendance_image', $meeting->post_attendance_image)
            ->where('meeting.post_attendance_message', $meeting->post_attendance_message)
        );
    }
}
